<?php

use LibreNMS\Config;

if ($device['os'] == 'netapp') {
    $netapp_storage = snmpwalk_cache_oid($device, 'dfEntry', null, 'NETAPP-MIB');

    if (is_array($netapp_storage)) {
        echo 'dfEntry ';
        foreach ($netapp_storage as $index => $storage) {
            $df_index = $storage['dfIndex'];
            $fstype = $storage['dfType'];
            $descr = $storage['dfFileSys'];
            $mounted_on = $storage['dfMountedOn'];
            $vserver = $storage['dfVserver'];
            $df_status = $storage['dfStatus'];
            $mirror_status = $storage['dfMirrorStatus'];
            $total_kb64 = $storage['df64TotalKBytes'];
            $reserved64 = $storage['df64TotalReservedKBytes'];
            $units = 1024;
            if (isset($total_kb64) && is_numeric($total_kb64) && isset($reserved64) && is_numeric($reserved64)) {
                $size = ($total_kb64 * $units);
                $used = ($storage['df64UsedKBytes'] * $units);
                $reserved = ($reserved64 * $units);
            } else {
                $size = ($storage['dfKBytesTotal'] * $units);
                $used = ($storage['dfKBytesUsed'] * $units);
                $reserved = '0';
            }


            if (is_numeric($index)) {
                discover_storage($valid_storage, $device, $index, $fstype, 'netapp-storage', $descr, $size, $units, $used);
		$netapp_df = dbFetchRow('SELECT * FROM `netapp_df` WHERE `index` = ? AND `device_id` = ?',
			array($df_index, $device['device_id']));

                $tmp_data = array(
                            'device_id' => $device['device_id'],
                            'file_sys' => $descr,
                            'index' => $df_index,
                            'kbytes_total' => $size,
                            'type' => $fstype,
                            'mounted_on' => $mounted_on,
                            'vserver' => $vserver,
                            'status' => $df_status,
                            'mirror_status' => $mirror_status,
                            'online' => $storage['dfStateOnline'],
                            'kbytes_percent' => $storage['dfPerCentKBytesCapacity'],
                            'inode_percent' => $storage['dfPerCentInodeCapacity'],
                            'max_files_possible' => $storage['dfMaxFilesPossible'],
                            'saved_percent' => $storage['dfPerCentSaved'],
                            'compress_saved_percent' => $storage['dfCompressSavedPercent'],
                            'dedupe_percent' => $storage['dfDedupeSavedPercent'],
                            'total_saved_percent' => $storage['dfTotalSavedPercent'],
                            'kbytes_reserved' => $reserved
                        );

                if ($netapp_df === false || !count($netapp_df)) {
                    $insert = dbInsert($tmp_data, 'netapp_df'); 
                    echo '+';
                } else {
                    $updated = dbUpdate($tmp_data, 'netapp_df', '`device_id` = ? AND `index` = ?', array($device['device_id'], $df_index));
                    if ($updated) {
                        echo 'U';
                    } else {
                        echo '.';
                    }
                }
            }
            unset($df_index, $mounted_on, $vserver, $df_status, $mirror_status, $tmp_data);
            unset($deny, $fstype, $descr, $size, $used, $units, $storage_rrd, $old_storage_rrd, $hrstorage_array);
        }
    }
}
