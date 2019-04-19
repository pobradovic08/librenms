<?php

if (is_numeric($vars['dfentry'])) {
    $graph_types = array(
                    'bytes' => 'Bytes',
                    'inodes' => 'Inodes',
                    'files' => 'Files',
                    'shared' => 'Shared',
                    'savings' => 'Savings'
                   );

    $i = 0;

    echo "<div style='margin: 0px;'><table class='table'>";
    echo "<tr><th width=320>Volume</th>";
    echo "<th width=320>Vserver</th>";
    echo "<th width=200>Capacity</th>";
    echo "<th width=200>Inodes</th>";
    echo "<th width=100>Saved</th>";
    echo "<th width=100>Type</th>";
    echo "<th width=200>Online / Status / Mirror</th></tr>";

    $i = '0';

    $netapp = dbFetchRows('SELECT * FROM `netapp_df` WHERE `device_id` = ? AND `df_id` = ?', array($device['device_id'], $vars['dfentry']));
    foreach ($netapp as $fs) {
        $bg_colour = is_integer($i / 2) ? $config['list_colour']['even'] : $config['list_colour']['odd'];

        if ($fs['online'] == 'true') {
            $fs_online_label = 'success';
        } else {
            $fs_online_label = 'danger';
        }

        switch ($fs['status']) {
            case 'mounted':
                $fs_status_label = 'success';
                break;
            case 'unmounted':
                $fs_status_label = 'default';
                break;
            default:
                $fs_status_label = 'warning';

        }

        switch ($fs['mirror_status']) {
            case 'normal':
                $fs_mirror_label = 'success';
                break;
            case 'failed':
            case 'degraded':
            case 'invalid':
                $fs_mirror_label = 'danger';
                break;
            case 'needcpcheck':
            case 'cpcheckwait':
            case 'resyncing':
            case 'invalid':
                $fs_mirror_label = 'warning';
                break;
            default:
                $fs_mirror_label = 'default';
        }

        $kbytes_bg = get_percentage_colours($fs['kbytes_percent'], 90);
        $inode_bg = get_percentage_colours($fs['inode_percent'], 90);
        if ($fs['total_saved_percent'] > 0) {
            $total_saved_span = "<span><b>{$fs['total_saved_percent']} %</b></span>";
        } else {
            $total_saved_span = "<span>{$fs['total_saved_percent']} %</span>";
        }

        echo "<tr bgcolor='$bg_colour'>";
        echo '<td><a href="' . generate_url($vars, array('dfentry' => $fs['df_id'], 'view' => null, 'graph' => null)) . '">' . $fs['file_sys'] . '</a></td>';
        echo "<td>{$fs['vserver']}</td>";

        echo "<td>" . print_percentage_bar(150, 20, $fs['kbytes_percent'], format_si($fs['kbytes_total']), 'ffffff', $kbytes_bg['left'], $fs['kbytes_percent'] . '%', 'ffffff', $kbytes_bg['right']) . "</td>";
        echo "<td>" . print_percentage_bar(150, 20, $fs['inode_percent'], null, 'ffffff', $inode_bg['left'], $fs['inode_percent'] . '%', 'ffffff', $inode_bg['right']) . "</td>";
        echo "<td>" . $total_saved_span . "</td>";

        echo "<td><span>{$fs['type']}</span></td>";
        echo "<td><span class='label label-" . $fs_online_label . "'>{$fs['online']}</span> / ";
        echo "<span class='label label-" . $fs_status_label . "'>{$fs['status']}</span> / ";
        echo "<span class='label label-" . $fs_mirror_label . "'>{$fs['mirror_status']}</span></td>";
        echo '</tr>';


        foreach ($graph_types as $graph_type => $graph_text) {
            $i++;
            echo '<tr class="list-bold" bgcolor="'.$bg_colour.'">';
            echo '<td colspan="9">';
            $graph_type            = 'netapp_'.$graph_type;
            $graph_array['height'] = '100';
            $graph_array['width']  = '213';
            $graph_array['to']     = $config['time']['now'];
            $graph_array['id']     = $fs['df_id'];
            $graph_array['type']   = $graph_type;

            echo '<h3>'.$graph_text.'</h3>';

            include 'includes/html/print-graphrow.inc.php';

            echo '
    </td>
    </tr>';
        }
    }

    echo '</table></div>';
} else {
    print_optionbar_start();

    echo "<span style='font-weight: bold;'>File systems</span> &#187; ";

    $menu_options = array('basic' => 'Basic');

    if (!$vars['view']) {
        $vars['view'] = 'basic';
    }

    $sep = '';
    foreach ($menu_options as $option => $text) {
        if ($vars['view'] == $option) {
            echo "<span class='pagemenu-selected'>";
        }

        echo '<a href="'.generate_url($vars, array('view' => 'basic', 'graph' => null)).'">'.$text.'</a>';
        if ($vars['view'] == $option) {
            echo '</span>';
        }

        echo ' | ';
    }

    unset($sep);
    echo ' Graphs: ';
    $graph_types = array(
                    'bytes' => 'Bytes',
                    'inodes' => 'Inodes',
                    'files' => 'Files',
                    'shared' => 'Shared',
                    'savings' => 'Savings'
                   );

    foreach ($graph_types as $type => $descr) {
        echo "$type_sep";
        if ($vars['graph'] == $type) {
            echo "<span class='pagemenu-selected'>";
        }

        echo '<a href="'.generate_url($vars, array('view' => 'graphs', 'graph' => $type)).'">'.$descr.'</a>';
        if ($vars['graph'] == $type) {
            echo '</span>';
        }

        $type_sep = ' | ';
    }

    print_optionbar_end();

    echo "<div style='margin: 0px;'><table class='table'>";
    echo "<tr><th width=320><a href=" . generate_url($vars, array('sort' => "file_sys")) . ">Volume</a></th>";
    echo "<th width=320>Vserver</th>";
    echo "<th width=200><a href=" . generate_url($vars, array('sort' => "kbytes_percent")) . ">Capacity</a></th>";
    echo "<th width=200><a href=" . generate_url($vars, array('sort' => "inode_percent")) . ">Inodes</a></th>";
    echo "<th width=100><a href=" . generate_url($vars, array('sort' => "total_saved_percent")) . ">Saved</a></th>";
    echo "<th width=100>Type</th>";
    echo "<th width=200>Online / Status / Mirror</th></tr>";

    $i = '0';

    $netapp = dbFetchRows('SELECT * FROM `netapp_df` WHERE `file_sys` NOT LIKE "%.snapshot" AND `device_id` = ?', array($device['device_id']));

    // Vserver sorting
    $valid_sort_keys = array('file_sys', 'kbytes_percent', 'inode_percent', 'total_saved_percent', 'type');
    if (isset($vars['sort']) && in_array($vars['sort'], $valid_sort_keys)) {
        $sort_key = $vars['sort'];
    } else {
        $sort_key = 'file_sys';
    }
    switch ($sort_key) {
        case 'kbytes_percent':
        case 'inode_percent':
        case 'total_saved_percent':
            $sort_direction = SORT_DESC;
            break;
        default:
            $sort_direction = SORT_ASC;
    }
    $netapp = array_sort_by_column($netapp, $sort_key, $sort_direction);

    foreach ($netapp as $fs) {
        $bg_colour = is_integer($i / 2) ? $config['list_colour']['even'] : $config['list_colour']['odd'];

        if ($fs['online'] == 'true') {
            $fs_online_label = 'success';
        } else {
            $fs_online_label = 'danger';
        }

        switch ($fs['status']) {
            case 'mounted':
                $fs_status_label = 'success';
                break;
            case 'unmounted':
                $fs_status_label = 'default';
                break;
            default:
                $fs_status_label = 'warning';

        }

        switch ($fs['mirror_status']) {
            case 'normal':
                $fs_mirror_label = 'success';
                break;
            case 'failed':
            case 'degraded':
            case 'invalid':
                $fs_mirror_label = 'danger';
                break;
            case 'needcpcheck':
            case 'cpcheckwait':
            case 'resyncing':
            case 'limbo':
                $fs_mirror_label = 'warning';
                break;
            default:
                $fs_mirror_label = 'default';
        }

        $kbytes_bg = get_percentage_colours($fs['kbytes_percent'], 90);
        $inode_bg = get_percentage_colours($fs['inode_percent'], 90);
        if ($fs['total_saved_percent'] > 0) {
            $total_saved_span = "<span><b>{$fs['total_saved_percent']} %</b></span>";
        } else {
            $total_saved_span = "<span>{$fs['total_saved_percent']} %</span>";
        }

        echo "<tr bgcolor='$bg_colour'>";
        echo '<td><a href="' . generate_url($vars, array('dfentry' => $fs['df_id'], 'view' => null, 'graph' => null)) . '">' . $fs['file_sys'] . '</a></td>';
        echo "<td>{$fs['vserver']}</td>";

        echo "<td>" . print_percentage_bar(150, 20, $fs['kbytes_percent'], format_si($fs['kbytes_total']), 'ffffff', $kbytes_bg['left'], $fs['kbytes_percent'] . '%', 'ffffff', $kbytes_bg['right']) . "</td>";
        echo "<td>" . print_percentage_bar(150, 20, $fs['inode_percent'], null, 'ffffff', $inode_bg['left'], $fs['inode_percent'] . '%', 'ffffff', $inode_bg['right']) . "</td>";
        echo "<td>" . $total_saved_span . "</td>";

        echo "<td><span>{$fs['type']}</span></td>";
        echo "<td><span class='label label-" . $fs_online_label . "'>{$fs['online']}</span> / ";
        echo "<span class='label label-" . $fs_status_label . "'>{$fs['status']}</span> / ";
        echo "<span class='label label-" . $fs_mirror_label . "'>{$fs['mirror_status']}</span></td>";
        echo '</tr>';


        if ($vars['view'] == 'graphs') {
            echo '<tr class="list-bold" bgcolor="'.$bg_colour.'">';
            echo '<td colspan="9">';
            $graph_type            = 'netapp_'.$vars['graph'];
            $graph_array['height'] = '100';
            $graph_array['width']  = '213';
            $graph_array['to']     = $config['time']['now'];
            $graph_array['id']     = $fs['df_id'];
            $graph_array['type']   = $graph_type;

            include 'includes/html/print-graphrow.inc.php';
            echo '
    </td>
    </tr>';
        }

        echo '</td>';
        echo '</tr>';

        $i++;
    }//end foreach

    echo '</table></div>';
}
