2019-09-09 12:43

# running php upgrade inspect see: https://github.com/silverstripe/silverstripe-upgrader
cd /var/www/upgrades/upgradeto4
php /var/www/upgrader/vendor/silverstripe/upgrader/bin/upgrade-code inspect /var/www/upgrades/upgradeto4/cms_edit_link_field/src  --root-dir=/var/www/upgrades/upgradeto4 --write -vvv
Writing changes for 0 files
Running post-upgrade on "/var/www/upgrades/upgradeto4/cms_edit_link_field/src"
[2019-09-09 12:43:24] Applying ApiChangeWarningsRule to CMSEditLinkField.php...
[2019-09-09 12:43:24] Applying UpdateVisibilityRule to CMSEditLinkField.php...
[2019-09-09 12:43:24] Applying ApiChangeWarningsRule to CMSEditLinkAPI.php...
[2019-09-09 12:43:25] Applying UpdateVisibilityRule to CMSEditLinkAPI.php...
unchanged:	Forms/Fields/CMSEditLinkField.php
Warnings for Forms/Fields/CMSEditLinkField.php:
 - Forms/Fields/CMSEditLinkField.php:67 SilverStripe\Forms\Formfield->dontEscape: FormField::$dontEscape has been removed. Escaping is now managed on a class by class basis.
Writing changes for 0 files
✔✔✔