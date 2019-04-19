<?php

use LibreNMS\RRD\RrdDefinition;

if (!is_array($storage_cache['netapp-storage'])) {
    $storage_cache['netapp-storage'] = snmpwalk_cache_oid($device, 'dfEntry', null, 'NETAPP-MIB');
    d_echo($storage_cache);
}

$entry = $storage_cache['netapp-storage'][$storage['storage_index']];

$storage['units'] = 1024;
if (isset($entry['df64TotalKBytes']) && is_numeric($entry['df64TotalKBytes'])) {
    $storage['used'] = ($entry['df64UsedKBytes'] * $storage['units']);
    $storage['size'] = ($entry['df64TotalKBytes'] * $storage['units']);
} else {
    $storage['used'] = ($entry['dfKBytesUsed'] * $storage['units']);
    $storage['size'] = ($entry['dfKBytesTotal'] * $storage['units']);
}

$storage['free'] = ($storage['size'] - $storage['used']);


$netapp_df = dbFetchRows('SELECT * FROM `netapp_df` WHERE `device_id` = ? AND `index` = ?', array($device['device_id'], $storage['storage_index']));

foreach ($netapp_df as $fs) {
    $df_index = $fs['index'];
    $rrd_name = array('storage', 'netapp', 'df', $df_index);
    $rrd_def = RrdDefinition::make()
        ->addDataset('dfInodesUsed', 'GAUGE', 0)
        ->addDataset('dfInodesFree', 'GAUGE', 0)
        ->addDataset('dfMaxFilesAvail', 'GAUGE', 0)
        ->addDataset('dfMaxFilesUsed', 'GAUGE', 0)
        ->addDataset('dfSisSharedKBytes', 'GAUGE', 0)
        ->addDataset('dfSisSavedKBytes', 'GAUGE', 0)
        ->addDataset('dfCompressSaved', 'GAUGE', 0)
        ->addDataset('dfDedupeSaved', 'GAUGE', 0)
        ->addDataset('dfTotalSaved', 'GAUGE', 0)
        ->addDataset('dfTotalReservedKBytes', 'GAUGE', 0)
        ->addDataset('dfUsedKBytes', 'GAUGE', 0)
        ->addDataset('dfAvailKBytes', 'GAUGE', 0);

    $fields = array(
        'dfInodesUsed' => round($entry['dfInodesUsed']),
        'dfInodesFree' => round($entry['dfInodesFree']),
        'dfMaxFilesAvail' => round($entry['dfMaxFilesAvail']),
        'dfMaxFilesUsed' => round($entry['dfMaxFilesUsed']),
        'dfSisSharedKBytes' => round($entry['df64SisSharedKBytes'] * $storage['units']),
        'dfSisSavedKBytes' => round($entry['df64SisSavedKBytes'] * $storage['units']),
        'dfCompressSaved' => round($entry['df64CompressSaved'] * $storage['units']),
        'dfDedupeSaved' => round($entry['df64DedupeSaved'] * $storage['units']),
        'dfTotalSaved' => round($entry['df64TotalSaved'] * $storage['units']),
        'dfTotalReservedKBytes' => round($entry['df64TotalReservedKBytes'] * $storage['units']),
        'dfUsedKBytes' => round($entry['df64UsedKBytes'] * $storage['units']),
        'dfAvailKBytes' => round($entry['df64AvailKBytes'] * $storage['units'])
    );

    $tags = compact('df_index', 'rrd_name', 'rrd_def');
    data_update($device, 'storage', $tags, $fields);

    $tmp_array = array(
        'file_sys' => $entry['dfFileSys'],
        'mounted_on' => $entry['dfMountedOn'],
        'vserver' => $entry['dfVserver'],
        'inode_percent' => $entry['dfPerCentInodeCapacity'],
        'kbytes_percent' => $entry['dfPerCentKBytesCapacity'],
        'total_saved_percent' => $entry['dfTotalSavedPercent'],
        'max_files_possible' => $entry['dfMaxFilesPossible'],
        'saved_percent' => $entry['dfPerCentSaved'],
        'compress_saved_percent' =>  $entry['dfCompressSavedPercent'],
        'dedupe_percent' => $entry['dfDedupeSavedPercent'],
        'kbytes_reserved' => $entry['df64TotalReservedKBytes'] * $storage['units'],
        'kbytes_total' => $entry['df64TotalKBytes'] * $storage['units'],
        'online' => $entry['dfStateOnline'],
        'status' => $entry['dfStatus'],
        'mirror_status' => $entry['dfMirrorStatus'],
        'type' => $entry['dfType']
    );

    $update = dbUpdate($tmp_array, 'netapp_df', '`df_id` = ?', array($fs['df_id']));
}
