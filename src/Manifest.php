<?php
namespace DavidBPirie\MunkiLDAPManifestPHP;

/**
 * Manifest class.
 */
class Manifest
{
    /**
     * ldap_server
     *
     * @var mixed
     * @access protected
     */
    protected $ldap_server;
    /**
     * ldap_username
     *
     * @var mixed
     * @access protected
     */
    protected $ldap_username;
    /**
     * ldap_password
     *
     * @var mixed
     * @access protected
     */
    protected $ldap_password;
    /**
     * ldap_basedn
     *
     * @var mixed
     * @access protected
     */
    protected $ldap_basedn;
    /**
     * ldap_connection
     *
     * @var mixed
     * @access protected
     */
    protected $ldap_connection;
    /**
     * components
     *
     * (default value: array())
     *
     * @var array
     * @access protected
     */
    protected $components = array();

    /**
     * __construct function.
     *
     * @access public
     * @param mixed $ldap_server
     * @param mixed $ldap_username
     * @param mixed $ldap_password
     * @param mixed $ldap_basedn
     * @param bool $ldap_bind_now (default: true)
     * @return void
     */
    public function __construct($ldap_server, $ldap_username, $ldap_password, $ldap_basedn, $ldap_bind_now = true)
    {
        $this->ldap_server = $ldap_server;
        $this->ldap_username = $ldap_username;
        $this->ldap_password = $ldap_password;
        $this->ldap_basedn = $ldap_basedn;
        if ($ldap_bind_now) {
            $this->ldapBind();
        }
    }

    /**
     * ldapBind function.
     * 
     * @access public
     * @return void
     */
    public function ldapBind()
    {
        $this->ldap_connection = ldap_connect($this->ldap_server);
        ldap_bind($this->ldap_connection, $this->ldap_username, $this->ldap_password);
    }
    
    /**
     * addComponent function.
     * 
     * @access public
     * @param mixed $name
     * @param mixed $dn_prefix
     * @param mixed $group_prefix
     * @return void
     */
    public function addComponent( $name, $dn_prefix, $group_prefix, $ldap_attribute = null)
    {
	    $this->components[$name] = new ManifestComponent( $name, $dn_prefix . $this->ldap_basedn, $group_prefix, $ldap_attribute);
    }
    
    /**
     * addComponents function.
     * 
     * @access public
     * @param array $component_parameters
     * @return void
     */
    public function addComponents( array $component_parameters )
    {
	    foreach( $component_parameters as $component_parameter )
	    {
		    if(
		    	!isset( $component_parameter["Name"] ) ||
		    	!isset( $component_parameter["DNPrefix"] ) ||
		    	!isset( $component_parameter["GroupPrefix"] )
		    ) {
                throw new \Exception(__METHOD__ . ": Expected parameter missing." );
		    }
		    $ldap_attribute = null;
		    if( isset( $component_parameter["LDAPAttribute"] ) ) {
			    $ldap_attribute = $component_parameter["LDAPAttribute"];
		    }
		    $this->addComponent( $component_parameter["Name"], $component_parameter["DNPrefix"], $component_parameter["GroupPrefix"], $ldap_attribute );
	    }
    }
    
    /**
     * generateLDAPEntries function.
     * 
     * @access public
     * @param mixed $client_id
     * @return void
     */
    public function generateLDAPEntries( $client_id )
    {
	    foreach( $this->components as $component )
	    {
		    $component->generateLDAPEntries( $this->ldap_connection, $client_id );
	    }
    }
    
    /**
     * addManualEntry function.
     * 
     * @access public
     * @param mixed $component_name
     * @param mixed $entry
     * @return void
     */
    public function addManualEntry( $component_name, $entry )
    {
	    if( !isset( $this->components[$component_name] ) ) {
                throw new \Exception(__METHOD__ . ": Component with name " . $component_name . " not found." );
	    }
	    $this->components[$component_name]->addManualEntry( $entry );
    }
    
    /**
     * addManualEntries function.
     * 
     * @access public
     * @param mixed $component_name
     * @param array $entries
     * @return void
     */
    public function addManualEntries( $component_name, array $entries )
    {
	    foreach( $entries as $entry ) {
		    $this->addManualEntry( $component_name, $entry );
	    }
    }
    
    /**
     * generateManifest function.
     * 
     * @access public
     * @return void
     */
    public function generateManifest()
    {
	    $manifest_dict = new \CFPropertyList\CFDictionary();
	    foreach( $this->components as $component_key => $component ) {
		    $manifest_dict->add( $component_key, $component->generateCFArray() );
	    }
	    $manifest = new \CFPropertyList\CFPropertyList();
	    $manifest->add( $manifest_dict );
	    return $manifest->toXML( true );
    }
}
