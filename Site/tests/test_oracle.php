<?php

// Connects to the MYDB database described in tnsnames.ora file,
// One example tnsnames.ora entry for MYDB could be:
//   MYDB =
//     (DESCRIPTION =
//       (ADDRESS = (PROTOCOL = TCP)(HOST = mymachine.oracle.com)(PORT = 1521))
//       (CONNECT_DATA =
//         (SERVER = DEDICATED)
//         (SERVICE_NAME = XE)
//       )
//     )

$conn = oci_pconnect('OMOP_RESULTS', 'OmoP10', 'LSOMOP');
if (!$conn) {
    $e = oci_error();
    trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
}

$stid = oci_parse($conn, 'SELECT * FROM METHOD_REF');
oci_execute($stid);

echo "<table border='1'>\n";
while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) {
    echo "<tr>\n";
    foreach ($row as $item) {
        echo "    <td>" . ($item !== null ? htmlentities($item, ENT_QUOTES) : "&nbsp;") . "</td>\n";
    }
    echo "</tr>\n";
}
echo "</table>\n";

?>
