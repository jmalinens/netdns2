# Request Signing - SIG(0) and TSIG #

Net\_DNS2 has support to sign outgoing requests using TSIG and SIG(0) (asymmetric private/public key) authentication.

Both Resolver (for zone transfers) and Updater requests can be signed using either authentication type.

## TSIG ##

A TSIG (Transaction SIGnature) can be added to the request to authenticate the request. See [RFC2845](http://tools.ietf.org/html/rfc2845) for more details.

In BIND, a zone can be setup to allow updates using a TSIG like:

```

key "mykey" {
    algorithm hmac-md5;
    secret "9dnf93asdf39fs";
};

zone "example.com" {
    type master;
    file "dynamic/example.com";

    allow-update {
        key "mykey";
    };
};

```

Then, using Net\_DNS2, you can execute:

```

//
// create a new Updater object
//
$u = new Net_DNS2_Updater('example.com', array('nameservers' => array('192.168.0.1')));

//
// add a TSIG to authenticate the request
//
$u->signTSIG('mykey', '9dnf93asdf39fs');

//
// send the update rquest.
//
$u->update();

```

## SIG(0) ##

Signing using SIG(0) is more complicated. It requires a private/public key to be generated. Both can be generated using the dnssec-keygen tool. This tool produces both the public key, which will be advertised via the domain zone, and a private key which is passed to the signSIG0() function.

Net\_DNS2 uses the PHP [OpenSSL Extension](http://ca2.php.net/manual/en/book.openssl.php) to support SIG(0). Net\_DNS2 will throw an exception if you try to sign requests using SIG(0) and you do not have openssl installed.

Unfortunately, the openssl extension is somewhat incomplete in PHP, and therefore Net\_DNS2 only supports RSA keys, and cannot support DSA keys.

```

//
// create a new Updater object
//
$u = new Net_DNS2_Updater('example.com', array('nameservers' => array('192.168.0.1')));

//
// add a SIG(0) to authenticate the request
//
$u->signSIG0('/etc/namedb/Kexample.com.+001+15765.private');

//
// send the update rquest.
//
$u->update();

```

## Zone Transfers (AXFR) ##

The sign signTSIG() and signSIG0() functions can be use to authenticate zone transfers:

```

//
// create a new Resolver object
//
$r = new Net_DNS2_Resolver(array('nameservers' => array('192.168.0.1')));

//
// add a SIG(0) to authenticate the request
//
$r->signSIG0('/etc/namedb/Kexample.com.+001+15765.private');

//
// send the update rquest.
//
$data = $r->query('example.com', 'AXFR');

```