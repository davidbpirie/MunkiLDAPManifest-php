# MunkiLDAPManifest-php
Manage Munki manifest content with LDAP via Computers and Groups objects.

## Overview
MunkiLDAPManifest-php provides a mechanism for delivering dynamically-generated munki manifest files by using LDAP objects - Computers objects for the device to receive the manifest, and Groups objects for the item to be included.

## Installation
1. Create a composer.json file in your manifests directory containing:
```
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/davidbpirie/MunkiLDAPManifest-php.git"
        }
    ],
    "require": {
        "davidbpirie/munkildapmanifest-php": "dev-master"
    }
}
```

2. Install using composer:

`composer install`

Note: you will need to have composer already installed. See https://getcomposer.org/doc/00-intro.md for instructions.

3. Copy the provided example_index.php to your manifests directory:

`cp vendor/DavidBPirie/MunkiLDAPManifest-php/example_index.php index.php`

4. Modify index.php to suit your environment - see the inline comments for guidance.

## Client configuration

Clients just need to have ManifestURL configured to point to your index.php file with a ? suffix eg:

`defaults write /Library/Preferences/ManagedInstalls ManifestURL -string "https://munki.example.com/manifests/index.php?"`

## Testing

On your server, you can run the index.php file via php's CLI providing the client identitifer as an argument to verify the output for that host, eg:

`php index.php client1.example.com`
