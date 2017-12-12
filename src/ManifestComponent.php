<?php
namespace DavidBPirie\MunkiLDAPManifestPHP;

/**
 * Manifest class.
 */
class ManifestComponent
{
    /**
     * manifest_key
     *
     * @var mixed
     * @access protected
     */
    protected $manifest_key;
    /**
     * ldap_dn
     *
     * @var mixed
     * @access protected
     */
    protected $ldap_dn;
    /**
     * ldap_attribute
     *
     * @var mixed
     * @access protected
     */
    protected $ldap_attribute;
    /**
     * group_prefix
     *
     * @var mixed
     * @access protected
     */
    protected $group_prefix;
    /**
     * entries_manual
     *
     * (default value: array())
     *
     * @var array
     * @access protected
     */
    protected $entries_manual = array();
    /**
     * entries_ldap
     *
     * (default value: array())
     *
     * @var array
     * @access protected
     */
    protected $entries_ldap = array();

    /**
     * __construct function.
     *
     * @access public
     * @param mixed $manifest_key
     * @param mixed $ldap_dn
     * @param mixed $group_prefix
     * @return void
     */
    public function __construct($manifest_key, $ldap_dn, $group_prefix, $ldap_attribute = null)
    {
        $this->manifest_key = $manifest_key;
        $this->ldap_dn = $ldap_dn;
        $this->group_prefix = $group_prefix;
        $this->ldap_attribute = $ldap_attribute;
    }

    /**
     * generateLDAPEntries function.
     *
     * @access public
     * @param mixed $ldap_connection
     * @param mixed $client_id
     * @return void
     */
    public function generateLDAPEntries($ldap_connection, $client_id)
    {
        $attributes = array( "member", "cn" );
        $ldap_attribute = $this->ldap_attribute;
        if( is_null( $ldap_attribute ) ) {
	        $ldap_attribute = "cn";
        }
        if (!in_array($ldap_attribute, $attributes)) {
            $attributes[] = $ldap_attribute;
        }
        $this->entries_ldap = array();
        $ldap_search = ldap_search($ldap_connection, $this->ldap_dn, "(cn=" . $this->group_prefix . "*)", $attributes);
        $ldap_entries = ldap_get_entries($ldap_connection, $ldap_search);
        foreach ($ldap_entries as $group_key => $group) {
            if (( $group_key !== "count" ) &&
                isset($group["member"]) &&
                isset($group[$ldap_attribute][0])
            ) {
                foreach ($group["member"] as $member_key => $member) {
                    if (( $member_key !== "count" ) &&
                        ( strpos($member, "CN=" . $client_id . ",") !== false )
                    ) {
	                    foreach( $group[$ldap_attribute] as $attribute_key => $attribute_value ) {
		                    if ( $attribute_key !== "count" ) {
			                    if( strpos( $attribute_value, $this->group_prefix ) === 0 ) {
				                    $this->entries_ldap[] = substr($attribute_value, strlen( $this->group_prefix));
			                    } else {
				                    $this->entries_ldap[] = $attribute_value;
			                    }
			                }
			            }
                        break;
                    }
                }
            }
        }
    }

    /**
     * addManualEntry function.
     *
     * @access public
     * @param mixed $entry
     * @return void
     */
    public function addManualEntry($entry)
    {
        $this->entries_manual[] = $entry;
    }

    /**
     * addManualEntries function.
     *
     * @access public
     * @param array $entries
     * @return void
     */
    public function addManualEntries(array $entries)
    {
        foreach ($entries as $entry) {
            $this->addManualEntry($entry);
        }
    }

    /**
     * generateCFArray function.
     *
     * @access public
     * @return void
     */
    public function generateCFArray()
    {
        $entry_cfarray = new \CFPropertyList\CFArray();
        $entries = array_unique(array_merge($this->entries_manual, $this->entries_ldap));
        foreach ($entries as $entry) {
            $entry_cfarray->add(new \CFPropertyList\CFString($entry));
        }
        return $entry_cfarray;
    }
}
