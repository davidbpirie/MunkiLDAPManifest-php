<?php
namespace DavidBPirie\MunkiLDAPManifestPHP;

require_once __DIR__ . '/vendor/autoload.php';

// LDAP connection config
$ldap_server    = "ldapserver.example.com";
$ldap_username  = "username";
$ldap_password  = "password";
$ldap_basedn    = "OU=Munki,DC=example,DC=com";

// LDAP manifest component config
/*
Each manifest_component entry must include:
    Name - what area it appears under in the manifest
    DNPrefix - where to search for the LDAP objects
    GroupPrefix - what each LDAP group name will be prefixed with
Optionally can also include:
    LDAPAttribute - where the value is stored in the LDAP group,
        eg url. If not provided, the group name suffix is used.
Only include manifest_components for the components you want
to manage from within LDAP.
*/

$manifest_components = array(
    array(
        "Name"              => "catalogs",
        "DNPrefix"          => "OU=Catalogs,",
        "GroupPrefix"       => "Munki_catalog_"
    ),
    array(
        "Name"              => "managed_installs",
        "DNPrefix"          => "OU=Managed Installs,",
        "GroupPrefix"       => "Munki_managed_install_"
    ),
    array(
        "Name"              => "included_manifests",
        "DNPrefix"          => "OU=Included Manifests,",
        "GroupPrefix"       => "Munki_included_manifest_"
    ),
    array(
        "Name"              => "managed_uninstalls",
        "DNPrefix"          => "OU=Managed Uninstalls,",
        "GroupPrefix"       => "Munki_managed_uninstall_"
    ),
    array(
        "Name"              => "optional_installs",
        "DNPrefix"          => "OU=Optional Installs,",
        "GroupPrefix"       => "Munki_optional_install_"
    ),
    array(
        "Name"              => "managed_updates",
        "DNPrefix"          => "OU=Managed Updates,",
        "GroupPrefix"       => "Munki_managed_update_"
    )
);

// Allow running via cli providing $query_string as an argument 
if( php_sapi_name() == "cli" ) {
	if( $argc < 2 ) {
		throw new \Exception("No query string provided." );
	}
	$query_string = $argv[1];
} else {
	$query_string = $_SERVER["QUERY_STRING"];
}

if( strstr( $query_string, "/" ) ) {
    // $query_strings including a "/" are interpreted as requesting the contents of a file
    if( file_exists( $query_string ) ) {
        echo file_get_contents( $query_string );
    } else {
        http_response_code(404);
        die();
    }
} else {
    // $query_strings not including a "/" are interpreted as a host identifier
	$host_name = $query_string;
	if( strpos( $host_name, "." ) === false ) {
		$client_id = $host_name;
	} else {
        // trim the domain name
		$client_id = substr( $host_name, 0, strpos( $host_name, "." ) );
	}
      
    $manifest = new Manifest(
        $ldap_server,
        $ldap_username,
        $ldap_password,
        $ldap_basedn
    );

    $manifest->addComponents( $manifest_components );
    
    // Always include this catalog
    $manifest->addManualEntry( "catalogs", "production" );
    
    // Optionally include any other static inclusions here
    // Note all included files must include a "/" ie be in a sub-directory
    // $manifest->addManualEntry( "included_manifests", "included_manifests/standard" );

    $manifest->generateLDAPEntries( $client_id );

    $manifest_xml = $manifest->GenerateManifest();
    echo $manifest_xml;
}
