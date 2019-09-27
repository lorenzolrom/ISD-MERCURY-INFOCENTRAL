<h1>Random Values for New Mercury Installation:</h1>
<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 9/27/2019
 * Time: 4:24 PM
 */

// Generate SALT for passwords, print to screen
$salt = hash('SHA512', openssl_random_pseudo_bytes(2048));

// Print password entry for default user
$password = password_hash($salt . 'MercuryPassword', PASSWORD_ARGON2ID);

// Generate new api key, print to screen
$secret = hash('SHA512', openssl_random_pseudo_bytes(2048));

?>
<table border="1">
    <tr>
        <td>SALT</td>
        <td><?=$salt?></td>
    </tr>
    <tr>
        <td>PASSWORD ('MercuryPassword')</td>
        <td><?=$password?></td>
    </tr>
    <tr>
        <td>SECRET</td>
        <td><?=$secret?></td>
    </tr>
</table>
<h2 style="color: red">These values are not tied to your installation, they should be used by the DBA to configure an initial installation!</h2>
