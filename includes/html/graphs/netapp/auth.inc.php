<?php

if (is_numeric($vars['id'])) {
    $df = dbFetchRow('SELECT * FROM `netapp_df` AS df, `devices` AS d WHERE df.df_id = ? AND df.device_id = d.device_id', array($vars['id']));

    if (is_numeric($df['device_id']) && ($auth || device_permitted($df['device_id']))) {
        $device = device_by_id_cache($df['device_id']);

        $rrd_filename = rrd_name($device['hostname'], array('storage', 'netapp-df', $df['index']));

        $title  = generate_device_link($device);
        $title .= ' :: Netapp File System :: '.htmlentities($df['file_sys']);
        $auth   = true;
    }
}
