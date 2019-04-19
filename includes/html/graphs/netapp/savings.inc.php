<?php

/***
 * Used for graphing two-component (ds1 and ds2) graphs where sum of
 * ds1 and ds2 is always the same value (total). For example this can
 * be used for graphing used and available disk space.
 */

$scale_min = '0';

$ds1 = 'dfCompressSaved';
$ds2 = 'dfDedupeSaved';
$units = sprintf('%-7s', 'Bytes');

require 'includes/html/graphs/common.inc.php';

// Data points
$rrd_options .= " DEF:ds1=$rrd_filename:$ds1:AVERAGE";
$rrd_options .= " DEF:ds2=$rrd_filename:$ds2:AVERAGE";
// Calculate top line (stacked)
$rrd_options .= ' CDEF:total=ds1,ds2,+';
$rrd_options .= " COMMENT:'  $units\t    Current\t\t    Average\t\t    Maximum\\n'";
$rrd_options .= " AREA:ds2#06858C66:";
$rrd_options .= " STACK:ds1#F4794222:";
$rrd_options .= " LINE1:ds2#06858Cff:'Deduplication'";
$rrd_options .= " GPRINT:ds2:LAST:'%6.2lf%s'";
$rrd_options .= " GPRINT:ds2:AVERAGE:'\t%6.2lf%s'";
$rrd_options .= " GPRINT:ds2:MAX:'\t%6.2lf%s\\n'";
$rrd_options .= " LINE1:total#692A4Eff:'Compression'";
$rrd_options .= " GPRINT:ds1:LAST:'  %6.2lf%s'";
$rrd_options .= " GPRINT:ds1:AVERAGE:'\t%6.2lf%s'";
$rrd_options .= " GPRINT:ds1:MAX:'\t%6.2lf%s\\n'";
$rrd_options .= " GPRINT:total:LAST:'  Total\t\t   %6.2lf%s\\n'";
