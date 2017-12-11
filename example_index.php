<?php
namespace DavidBPirie\MunkiLDAPManifestPHP;

require_once __DIR__ . '/vendor/autoload.php';

if( php_sapi_name() == "cli" ) {
	if( $argc < 2 ) {
		throw new \Exception("No query string provided." );
	}
	$query_string = $argv[1];
} else {
	$query_string = $_SERVER["QUERY_STRING"];
}

if( strstr( $query_string, "/" ) ) {
    if( file_exists( $query_string ) ) {
        echo file_get_contents( $query_string );
    } else {
        http_response_code(404);
        die();
    }
} else {
	$client_id = $query_string;
    
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
    
    $manifest = new Manifest(
        "ldapserver.example.com",
        "username",
        "password",
        "OU=Munki,DC=example,DC=com"
    );

    $manifest->addComponents( $manifest_components );
    
    $manifest->addManualEntry( "catalogs", "prod" );
    
    $manifest->generateLDAPEntries( $client_id );

    $manifest_xml = $manifest->GenerateManifest();
    echo $manifest_xml;
}
