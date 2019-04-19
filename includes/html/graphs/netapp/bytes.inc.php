<?php

/***
 * Used for graphing two-component (ds1 and ds2) graphs where sum of
 * ds1 and ds2 is always the same value (total). For example this can
 * be used for graphing used and available disk space.
 */

$scale_min = '0';

$ds1 = 'dfUsedKBytes';
$ds2 = 'dfAvailKBytes';
$units = sprintf('%-7s', 'Bytes');

require 'includes/html/graphs/common.inc.php';

// Data points
$rrd_options .= " DEF:ds1=$rrd_filename:$ds1:AVERAGE";
$rrd_options .= " DEF:ds2=$rrd_filename:$ds2:AVERAGE";
// Calculate top line (stacked)
$rrd_options .= ' CDEF:total=ds1,ds2,+';
$rrd_options .= " COMMENT:'  $units\t   Current\t\t    Average\t\t    Maximum\\n'";
$rrd_options .= " AREA:ds2#2E8C1466:";
$rrd_options .= " STACK:ds1#F2413022:";
$rrd_options .= " LINE2:ds2#2E8C14ff:'Available'";
$rrd_options .= " GPRINT:ds2:LAST:'   %6.2lf%s'";
$rrd_options .= " GPRINT:ds2:AVERAGE:'\t%6.2lf%s'";
$rrd_options .= " GPRINT:ds2:MAX:'\t%6.2lf%s\\n'";
$rrd_options .= " LINE2:total#F24130ff:'Used'";
$rrd_options .= " GPRINT:ds1:LAST:'   \t%6.2lf%s'";
$rrd_options .= " GPRINT:ds1:AVERAGE:'\t%6.2lf%s'";
$rrd_options .= " GPRINT:ds1:MAX:'\t%6.2lf%s\\n'";
$rrd_options .= " GPRINT:total:LAST:'  Total\t\t   %6.2lf%s\\n'";
