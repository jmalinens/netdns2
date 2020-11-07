<?php

/**
 * DNS Library for handling lookups and updates. 
 *
 * Copyright (c) 2020, Mike Pultz <mike@mikepultz.com>. All rights reserved.
 *
 * See LICENSE for more details.
 *
 * @category  Networking
 * @package   NetDNS2
 * @author    Mike Pultz <mike@mikepultz.com>
 * @copyright 2020 Mike Pultz <mike@mikepultz.com>
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      https://netdns2.com/
 * @since     File available since Release 0.6.0
 *
 */

namespace NetDNS2;

//
// initalize the packet id value
//
\NetDNS2\Lookups::$next_packet_id   = mt_rand(0, 65535);

//
// build the reverse lookup tables; this is just so we don't have to
// have duplicate static content laying around.
//
\NetDNS2\Lookups::$rr_types_by_id       = array_flip(\NetDNS2\Lookups::$rr_types_by_name);
\NetDNS2\Lookups::$classes_by_id        = array_flip(\NetDNS2\Lookups::$classes_by_name);
\NetDNS2\Lookups::$rr_types_class_to_id = array_flip(\NetDNS2\Lookups::$rr_types_id_to_class);
\NetDNS2\Lookups::$algorithm_name_to_id = array_flip(\NetDNS2\Lookups::$algorithm_id_to_name);
\NetDNS2\Lookups::$digest_name_to_id    = array_flip(\NetDNS2\Lookups::$digest_id_to_name);
\NetDNS2\Lookups::$rr_qtypes_by_id      = array_flip(\NetDNS2\Lookups::$rr_qtypes_by_name);
\NetDNS2\Lookups::$rr_metatypes_by_id   = array_flip(\NetDNS2\Lookups::$rr_metatypes_by_name);
\NetDNS2\Lookups::$protocol_by_id       = array_flip(\NetDNS2\Lookups::$protocol_by_name);

/**
 * This class provides simple lookups used througout the NetDNS2 code
 * 
 */
class Lookups
{
    /*
     * size (in bytes) of a header in a standard DNS packet
     */
    const DNS_HEADER_SIZE       = 12;

    /*
     * max size of a UDP packet
     */
    const DNS_MAX_UDP_SIZE      = 512;
    
    /*
     * Query/Response flag
     */
    const QR_QUERY              = 0;        // RFC 1035
    const QR_RESPONSE           = 1;        // RFC 1035

    /*
     * DNS Op Codes
     */
    const OPCODE_QUERY          = 0;        // RFC 1035
    const OPCODE_IQUERY         = 1;        // RFC 1035, RFC 3425
    const OPCODE_STATUS         = 2;        // RFC 1035
    const OPCODE_NOTIFY         = 4;        // RFC 1996
    const OPCODE_UPDATE         = 5;        // RFC 2136
    const OPCODE_DSO            = 6;        // RFC 8490

    /*
     * Resource Record Classes
     */
    const RR_CLASS_IN           = 1;        // RFC 1035
    const RR_CLASS_CH           = 3;        // RFC 1035
    const RR_CLASS_HS           = 4;        // RFC 1035
    const RR_CLASS_NONE         = 254;      // RFC 2136
    const RR_CLASS_ANY          = 255;      // RFC 1035

    /*
     * DNS Response Codes
     */
    const RCODE_NOERROR         = 0;        // RFC 1035
    const RCODE_FORMERR         = 1;        // RFC 1035
    const RCODE_SERVFAIL        = 2;        // RFC 1035
    const RCODE_NXDOMAIN        = 3;        // RFC 1035
    const RCODE_NOTIMP          = 4;        // RFC 1035
    const RCODE_REFUSED         = 5;        // RFC 1035
    const RCODE_YXDOMAIN        = 6;        // RFC 2136
    const RCODE_YXRRSET         = 7;        // RFC 2136
    const RCODE_NXRRSET         = 8;        // RFC 2136
    const RCODE_NOTAUTH         = 9;        // RFC 2136
    const RCODE_NOTZONE         = 10;       // RFC 2136
    const RCODE_DSOTYPENI       = 11;       // RFC 8490

    // 12-15 reserved

    const RCODE_BADSIG          = 16;       // RFC 2845
    const RCODE_BADVERS         = 16;       // RFC 6891    
    const RCODE_BADKEY          = 17;       // RFC 2845
    const RCODE_BADTIME         = 18;       // RFC 2845
    const RCODE_BADMODE         = 19;       // RFC 2930
    const RCODE_BADNAME         = 20;       // RFC 2930
    const RCODE_BADALG          = 21;       // RFC 2930
    const RCODE_BADTRUNC        = 22;       // RFC 4635
    const RCODE_BADCOOKIE       = 23;       // RFC 7873

    /*
     * internal errors codes returned by the exceptions class
     */
    const E_NONE                = 0;
    const E_DNS_FORMERR         = self::RCODE_FORMERR;
    const E_DNS_SERVFAIL        = self::RCODE_SERVFAIL;
    const E_DNS_NXDOMAIN        = self::RCODE_NXDOMAIN;
    const E_DNS_NOTIMP          = self::RCODE_NOTIMP;
    const E_DNS_REFUSED         = self::RCODE_REFUSED;
    const E_DNS_YXDOMAIN        = self::RCODE_YXDOMAIN;
    const E_DNS_YXRRSET         = self::RCODE_YXRRSET;
    const E_DNS_NXRRSET         = self::RCODE_NXRRSET;
    const E_DNS_NOTAUTH         = self::RCODE_NOTAUTH;
    const E_DNS_NOTZONE         = self::RCODE_NOTZONE;

    // 11-15 reserved

    const E_DNS_BADSIG          = self::RCODE_BADSIG;
    const E_DNS_BADKEY          = self::RCODE_BADKEY;
    const E_DNS_BADTIME         = self::RCODE_BADTIME;
    const E_DNS_BADMODE         = self::RCODE_BADMODE;
    const E_DNS_BADNAME         = self::RCODE_BADNAME;
    const E_DNS_BADALG          = self::RCODE_BADALG;
    const E_DNS_BADTRUNC        = self::RCODE_BADTRUNC;    
    const E_DNS_BADCOOKIE       = self::RCODE_BADCOOKIE;

    // other error conditions

    const E_NS_INVALID_FILE     = 200;
    const E_NS_INVALID_ENTRY    = 201;
    const E_NS_FAILED           = 202;
    const E_NS_SOCKET_FAILED    = 203;
    const E_NS_INVALID_SOCKET   = 204;

    const E_PACKET_INVALID      = 300;
    const E_PARSE_ERROR         = 301;
    const E_HEADER_INVALID      = 302;
    const E_QUESTION_INVALID    = 303;
    const E_RR_INVALID          = 304;

    const E_OPENSSL_ERROR       = 400;
    const E_OPENSSL_UNAVAIL     = 401;
    const E_OPENSSL_INV_PKEY    = 402;
    const E_OPENSSL_INV_ALGO    = 403;

    const E_CACHE_UNSUPPORTED   = 500;
    const E_CACHE_SHM_FILE      = 501;
    const E_CACHE_SHM_UNAVAIL   = 502;

    /*
     * EDNS0 Option Codes (OPT)
     */
    // 0 - Reserved
    const EDNS0_OPT_LLQ             = 1;
    const EDNS0_OPT_UL              = 2;
    const EDNS0_OPT_NSID            = 3;
    // 4 - Reserved
    const EDNS0_OPT_DAU             = 5;
    const EDNS0_OPT_DHU             = 6;
    const EDNS0_OPT_N3U             = 7;
    const EDNS0_OPT_CLIENT_SUBNET   = 8;
    const EDNS0_OPT_EXPIRE          = 9;
    const EDNS0_OPT_COOKIE          = 10;
    const EDNS0_OPT_TCP_KEEPALIVE   = 11;
    const EDNS0_OPT_PADDING         = 12;
    const EDNS0_OPT_CHAIN           = 13;
    const EDNS0_OPT_KEY_TAG         = 14;
    // 15 - unsassigned
    const EDNS0_OPT_CLIENT_TAG      = 16;
    const EDNS0_OPT_SERVER_TAG      = 17;
    // 18-26945 - unassigned
    const EDNS0_OPT_DEVICEID        = 26946;

    /*
     * DNSSEC Algorithms
     */
    const DNSSEC_ALGORITHM_RES                  = 0;
    const DNSSEC_ALGORITHM_RSAMD5               = 1;
    const DNSSEC_ALGORITHM_DH                   = 2;
    const DNSSEC_ALGORITHM_DSA                  = 3;
    const DNSSEC_ALGORITHM_ECC                  = 4;
    const DNSSEC_ALGORITHM_RSASHA1              = 5;
    const DNSSEC_ALGORITHM_DSANSEC3SHA1         = 6;
    const DSNSEC_ALGORITHM_RSASHA1NSEC3SHA1     = 7;
    const DNSSEC_ALGORITHM_RSASHA256	        = 8;
    const DNSSEC_ALGORITHM_RSASHA512            = 10;
    const DNSSEC_ALGORITHM_ECCGOST              = 12;
    const DNSSEC_ALGORITHM_ECDSAP256SHA256      = 13;
    const DNSSEC_ALGORITHM_ECDSAP384SHA384      = 14;
    const DNSSEC_ALGORITHM_ED25519              = 15;
    const DNSSEC_ALGORITHM_ED448                = 16;
    const DNSSEC_ALGORITHM_INDIRECT             = 252;
    const DNSSEC_ALGORITHM_PRIVATEDNS           = 253;
    const DNSSEC_ALGORITHM_PRIVATEOID           = 254;

    /*
     * DNSSEC Digest Types
     */
    const DNSSEC_DIGEST_RES                     = 0;
    const DNSSEC_DIGEST_SHA1                    = 1;
    const DNSSEC_DIGEST_SHA256                  = 2;
    const DNSSEC_DIGEST_GOST                    = 3;
    const DNSSEC_DIGEST_SHA384                  = 4;

    /*
     * The packet id used when sending requests
     */
    public static $next_packet_id;

    /*
     * Used to map resource record types to their id's, and back
     */
    public static $rr_types_by_id   = [];
    public static $rr_types_by_name = [

        'SIG0'          => 0,       // RFC 2931 pseudo type
        'A'             => 1,       // RFC 1035
        'NS'            => 2,       // RFC 1035
        'MD'            => 3,       // RFC 1035 - obsolete, Not implemented
        'MF'            => 4,       // RFC 1035 - obsolete, Not implemented
        'CNAME'         => 5,       // RFC 1035
        'SOA'           => 6,       // RFC 1035
        'MB'            => 7,       // RFC 1035 - obsolete, Not implemented
        'MG'            => 8,       // RFC 1035 - obsolete, Not implemented
        'MR'            => 9,       // RFC 1035 - obsolete, Not implemented
        'NULL'          => 10,      // RFC 1035 - obsolete, Not implemented
        'WKS'           => 11,      // RFC 1035
        'PTR'           => 12,      // RFC 1035
        'HINFO'         => 13,      // RFC 1035
        'MINFO'         => 14,      // RFC 1035 - obsolete, Not implemented
        'MX'            => 15,      // RFC 1035
        'TXT'           => 16,      // RFC 1035
        'RP'            => 17,      // RFC 1183
        'AFSDB'         => 18,      // RFC 1183
        'X25'           => 19,      // RFC 1183
        'ISDN'          => 20,      // RFC 1183
        'RT'            => 21,      // RFC 1183
        'NSAP'          => 22,      // RFC 1706
        'NSAP_PTR'      => 23,      // RFC 1348 - obsolete, Not implemented
        'SIG'           => 24,      // RFC 2535
        'KEY'           => 25,      // RFC 2535, RFC 2930
        'PX'            => 26,      // RFC 2163
        'GPOS'          => 27,      // RFC 1712 - Not implemented
        'AAAA'          => 28,      // RFC 3596
        'LOC'           => 29,      // RFC 1876
        'NXT'           => 30,      // RFC 2065, obsoleted by by RFC 3755
        'EID'           => 31,      // [Patton][Patton1995]
        'NIMLOC'        => 32,      // [Patton][Patton1995]
        'SRV'           => 33,      // RFC 2782
        'ATMA'          => 34,      // Windows only
        'NAPTR'         => 35,      // RFC 2915
        'KX'            => 36,      // RFC 2230
        'CERT'          => 37,      // RFC 4398
        'A6'            => 38,      // downgraded to experimental by RFC 3363
        'DNAME'         => 39,      // RFC 2672
        'SINK'          => 40,      // Not implemented
        'OPT'           => 41,      // RFC 2671
        'APL'           => 42,      // RFC 3123
        'DS'            => 43,      // RFC 4034
        'SSHFP'         => 44,      // RFC 4255
        'IPSECKEY'      => 45,      // RFC 4025
        'RRSIG'         => 46,      // RFC 4034
        'NSEC'          => 47,      // RFC 4034
        'DNSKEY'        => 48,      // RFC 4034
        'DHCID'         => 49,      // RFC 4701
        'NSEC3'         => 50,      // RFC 5155
        'NSEC3PARAM'    => 51,      // RFC 5155
        'TLSA'          => 52,      // RFC 6698
        'SMIMEA'        => 53,      // RFC 8162

                                    // 54 unassigned

        'HIP'           => 55,      // RFC 5205
        'NINFO'         => 56,      // Not implemented
        'RKEY'          => 57,      // Not implemented
        'TALINK'        => 58,      // 
        'CDS'           => 59,      // RFC 7344
        'CDNSKEY'       => 60,      // RFC 7344
        'OPENPGPKEY'    => 61,      // RFC 7929
        'CSYNC'         => 62,      // RFC 7477
        'ZONEMD'        => 63,      // Not implemented yet
        'SVCB'          => 64,      // Not implemented yet
        'HTTPS'         => 65,      // Not implemented yet

                                    // 66 - 98 unassigned

        'SPF'           => 99,      // RFC 4408
        'UINFO'         => 100,     // no RFC, Not implemented
        'UID'           => 101,     // no RFC, Not implemented
        'GID'           => 102,     // no RFC, Not implemented
        'UNSPEC'        => 103,     // no RFC, Not implemented
        'NID'           => 104,     // RFC 6742
        'L32'           => 105,     // RFC 6742
        'L64'           => 106,     // RFC 6742
        'LP'            => 107,     // RFC 6742
        'EUI48'         => 108,     // RFC 7043
        'EUI64'         => 109,     // RFC 7043

                                    // 110 - 248 unassigned

        'TKEY'          => 249,     // RFC 2930
        'TSIG'          => 250,     // RFC 2845
        'IXFR'          => 251,     // RFC 1995 - only a full (AXFR) is supported
        'AXFR'          => 252,     // RFC 1035
        'MAILB'         => 253,     // RFC 883, Not implemented
        'MAILA'         => 254,     // RFC 973, Not implemented
        'ANY'           => 255,     // RFC 1035 - we support both 'ANY' and '*'
        'URI'           => 256,     // RFC 7553
        'CAA'           => 257,     // RFC 8659
        'AVC'           => 258,     // Application Visibility and Control
        'DOA'           => 259,     // Not implemented yet
        'AMTRELAY'      => 260,     // RFC 8777

                                    // 261 - 32767 unassigned

        'TA'            => 32768,   // same as DS
        'DLV'           => 32769,   // RFC 4431
        'TYPE65534'     => 65534    // Private Bind record
    ];

    /*
     * Qtypes and Metatypes - defined in RFC2929 section 3.1
     */
    public static $rr_qtypes_by_id      = [];
    public static $rr_qtypes_by_name    = [

        'IXFR'          => 251,     // RFC 1995 - only a full (AXFR) is supported
        'AXFR'          => 252,     // RFC 1035
        'MAILB'         => 253,     // RFC 883, Not implemented
        'MAILA'         => 254,     // RFC 973, Not implemented
        'ANY'           => 255      // RFC 1035 - we support both 'ANY' and '*'
    ];
    
    public static $rr_metatypes_by_id   = [];
    public static $rr_metatypes_by_name = [

        'OPT'           => 41,      // RFC 2671
        'TKEY'          => 249,     // RFC 2930
        'TSIG'          => 250      // RFC 2845
    ];

    /*
     * used to map resource record id's to RR class names
     */
    public static $rr_types_class_to_id = [];
    public static $rr_types_id_to_class = [

        1           => 'NetDNS2\RR\A',
        2           => 'NetDNS2\RR\NS',
        5           => 'NetDNS2\RR\CNAME',
        6           => 'NetDNS2\RR\SOA',
        11          => 'NetDNS2\RR\WKS',
        12          => 'NetDNS2\RR\PTR',
        13          => 'NetDNS2\RR\HINFO',
        15          => 'NetDNS2\RR\MX',
        16          => 'NetDNS2\RR\TXT',
        17          => 'NetDNS2\RR\RP',
        18          => 'NetDNS2\RR\AFSDB',
        19          => 'NetDNS2\RR\X25',
        20          => 'NetDNS2\RR\ISDN',
        21          => 'NetDNS2\RR\RT',
        22          => 'NetDNS2\RR\NSAP',
        24          => 'NetDNS2\RR\SIG',
        25          => 'NetDNS2\RR\KEY',
        26          => 'NetDNS2\RR\PX',
        28          => 'NetDNS2\RR\AAAA',
        29          => 'NetDNS2\RR\LOC',
        31          => 'NetDNS2\RR\EID',
        32          => 'NetDNS2\RR\NIMLOC',
        33          => 'NetDNS2\RR\SRV',
        34          => 'NetDNS2\RR\ATMA',
        35          => 'NetDNS2\RR\NAPTR',
        36          => 'NetDNS2\RR\KX',
        37          => 'NetDNS2\RR\CERT',
        39          => 'NetDNS2\RR\DNAME',
        41          => 'NetDNS2\RR\OPT',
        42          => 'NetDNS2\RR\APL',
        43          => 'NetDNS2\RR\DS',
        44          => 'NetDNS2\RR\SSHFP',
        45          => 'NetDNS2\RR\IPSECKEY',
        46          => 'NetDNS2\RR\RRSIG',
        47          => 'NetDNS2\RR\NSEC',
        48          => 'NetDNS2\RR\DNSKEY',
        49          => 'NetDNS2\RR\DHCID',
        50          => 'NetDNS2\RR\NSEC3',
        51          => 'NetDNS2\RR\NSEC3PARAM',
        52          => 'NetDNS2\RR\TLSA',
        53          => 'NetDNS2\RR\SMIMEA',
        55          => 'NetDNS2\RR\HIP',
        58          => 'NetDNS2\RR\TALINK',
        59          => 'NetDNS2\RR\CDS',
        60          => 'NetDNS2\RR\CDNSKEY',
        61          => 'NetDNS2\RR\OPENPGPKEY',
        62          => 'NetDNS2\RR\CSYNC',
        99          => 'NetDNS2\RR\SPF',
        104         => 'NetDNS2\RR\NID',
        105         => 'NetDNS2\RR\L32',
        106         => 'NetDNS2\RR\L64',
        107         => 'NetDNS2\RR\LP',
        108         => 'NetDNS2\RR\EUI48',
        109         => 'NetDNS2\RR\EUI64',

        249         => 'NetDNS2\RR\TKEY',
        250         => 'NetDNS2\RR\TSIG',

    //    251            - IXFR - handled as a full zone transfer (252)
    //    252            - AXFR - handled as a function call

        255         => 'NetDNS2\RR\ANY',
        256         => 'NetDNS2\RR\URI',
        257         => 'NetDNS2\RR\CAA',
        258         => 'NetDNS2\RR\AVC',
        260         => 'NetDNS2\RR\AMTRELAY',
        32768       => 'NetDNS2\RR\TA',
        32769       => 'NetDNS2\RR\DLV',
        65534       => 'NetDNS2\RR\TYPE65534'
    ];

    /*
     * used to map resource record class names to their id's, and back
     */
    public static $classes_by_id    = [];
    public static $classes_by_name  = [

        'IN'    => self::RR_CLASS_IN,        // RFC 1035
        'CH'    => self::RR_CLASS_CH,        // RFC 1035
        'HS'    => self::RR_CLASS_HS,        // RFC 1035
        'NONE'  => self::RR_CLASS_NONE,      // RFC 2136
        'ANY'   => self::RR_CLASS_ANY        // RFC 1035
    ];

    /*
     * maps response codes to error messages
     */
    public static $result_code_messages = [

        self::RCODE_NOERROR     => 'The request completed successfully.',
        self::RCODE_FORMERR     => 'The name server was unable to interpret the query.',
        self::RCODE_SERVFAIL    => 'The name server was unable to process this query due to a problem with the name server.',
        self::RCODE_NXDOMAIN    => 'The domain name referenced in the query does not exist.',
        self::RCODE_NOTIMP      => 'The name server does not support the requested kind of query.',
        self::RCODE_REFUSED     => 'The name server refuses to perform the specified operation for policy reasons.',
        self::RCODE_YXDOMAIN    => 'Name Exists when it should not.',
        self::RCODE_YXRRSET     => 'RR Set Exists when it should not.',
        self::RCODE_NXRRSET     => 'RR Set that should exist does not.',
        self::RCODE_NOTAUTH     => 'Server Not Authoritative for zone.',
        self::RCODE_NOTZONE     => 'Name not contained in zone.',

        self::RCODE_BADSIG      => 'TSIG Signature Failure.',
        self::RCODE_BADKEY      => 'Key not recognized.',
        self::RCODE_BADTIME     => 'Signature out of time window.',
        self::RCODE_BADMODE     => 'Bad TKEY Mode.',
        self::RCODE_BADNAME     => 'Duplicate key name.',
        self::RCODE_BADALG      => 'Algorithm not supported.',
        self::RCODE_BADTRUNC    => 'Bad truncation.'
    ];

    /*
     * maps DNS SEC alrorithms to their mnemonics
     */
    public static $algorithm_name_to_id = [];
    public static $algorithm_id_to_name = [
    
        self::DNSSEC_ALGORITHM_RES                  => 'RES',
        self::DNSSEC_ALGORITHM_RSAMD5               => 'RSAMD5',
        self::DNSSEC_ALGORITHM_DH                   => 'DH',
        self::DNSSEC_ALGORITHM_DSA                  => 'DSA',
        self::DNSSEC_ALGORITHM_ECC                  => 'ECC',
        self::DNSSEC_ALGORITHM_RSASHA1              => 'RSASHA1',
        self::DNSSEC_ALGORITHM_DSANSEC3SHA1         => 'DSA-NSEC3-SHA1',
        self::DSNSEC_ALGORITHM_RSASHA1NSEC3SHA1     => 'RSASHA1-NSEC3-SHA1',
        self::DNSSEC_ALGORITHM_RSASHA256            => 'RSASHA256',
        self::DNSSEC_ALGORITHM_RSASHA512            => 'RSASHA512',
        self::DNSSEC_ALGORITHM_ECCGOST              => 'ECC-GOST',
        self::DNSSEC_ALGORITHM_ECDSAP256SHA256      => 'ECDSAP256SHA256',
        self::DNSSEC_ALGORITHM_ECDSAP384SHA384      => 'ECDSAP384SHA384',
        self::DNSSEC_ALGORITHM_ED25519              => 'ED25519',
        self::DNSSEC_ALGORITHM_ED448                => 'ED448',
        self::DNSSEC_ALGORITHM_INDIRECT             => 'INDIRECT',
        self::DNSSEC_ALGORITHM_PRIVATEDNS           => 'PRIVATEDNS',
        self::DNSSEC_ALGORITHM_PRIVATEOID           => 'PRIVATEOID'
    ];

    /*
     * maps DNSSEC digest types to their mnemonics
     */
    public static $digest_name_to_id = [];
    public static $digest_id_to_name = [

        self::DNSSEC_DIGEST_RES         => 'RES',
        self::DNSSEC_DIGEST_SHA1        => 'SHA-1',
        self::DNSSEC_DIGEST_SHA256      => 'SHA-256',
        self::DNSSEC_DIGEST_GOST        => 'GOST-R-34.11-94',
        self::DNSSEC_DIGEST_SHA384      => 'SHA-384'
    ];

    /*
     * Protocols names - RFC 1010
     */
    public static $protocol_by_id   = [];
    public static $protocol_by_name = [

        'ICMP'          => 1,
        'IGMP'          => 2,
        'GGP'           => 3,
        'ST'            => 5,
        'TCP'           => 6,
        'UCL'           => 7,
        'EGP'           => 8,
        'IGP'           => 9,
        'BBN-RCC-MON'   => 10,
        'NVP-II'        => 11,
        'PUP'           => 12,
        'ARGUS'         => 13,
        'EMCON'         => 14,
        'XNET'          => 15,
        'CHAOS'         => 16,
        'UDP'           => 17,
        'MUX'           => 18,
        'DCN-MEAS'      => 19,
        'HMP'           => 20,
        'PRM'           => 21,
        'XNS-IDP'       => 22,
        'TRUNK-1'       => 23,
        'TRUNK-2'       => 24,
        'LEAF-1'        => 25,
        'LEAF-2'        => 26,
        'RDP'           => 27,
        'IRTP'          => 28,
        'ISO-TP4'       => 29,
        'NETBLT'        => 30,
        'MFE-NSP'       => 31,
        'MERIT-INP'     => 32,
        'SEP'           => 33,
        // 34 - 60      - Unassigned
        // 61           - any host internal protocol
        'CFTP'          => 62,
        // 63           - any local network
        'SAT-EXPAK'     => 64,
        'MIT-SUBNET'    => 65,
        'RVD'           => 66,
        'IPPC'          => 67,
        // 68           - any distributed file system
        'SAT-MON'       => 69,
        // 70           - Unassigned
        'IPCV'          => 71,
        // 72 - 75      - Unassigned
        'BR-SAT-MON'    => 76,
        // 77           - Unassigned
        'WB-MON'        => 78,
        'WB-EXPAK'      => 79
        // 80 - 254     - Unassigned
        // 255          - Reserved
    ];
}