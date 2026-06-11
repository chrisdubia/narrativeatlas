<?php

$is = is_readable("/home/customer/www/narrativeatlas.org/keys/private_key.pem");
echo  $is? "Yes" : "No";