<?php
unset( $_SESSION[ 'id' ]);
unset( $_SESSION[ 'login' ]);
header( 'Location: /' );
exit()
?>
