2019-09-09 12:41

# running php upgrade upgrade see: https://github.com/silverstripe/silverstripe-upgrader
cd /var/www/upgrades/upgradeto4
php /var/www/upgrader/vendor/silverstripe/upgrader/bin/upgrade-code upgrade /var/www/upgrades/upgradeto4/cms_edit_link_field  --root-dir=/var/www/upgrades/upgradeto4 --write -vvv --prompt
Writing changes for 3 files
Running upgrades on "/var/www/upgrades/upgradeto4/cms_edit_link_field"
[2019-09-09 12:41:37] Applying RenameClasses to CMSEditLinkField.php...
[2019-09-09 12:41:37] Applying ClassToTraitRule to CMSEditLinkField.php...
[2019-09-09 12:41:37] Applying RenameClasses to CMSEditLinkAPI.php...
[2019-09-09 12:41:37] Applying ClassToTraitRule to CMSEditLinkAPI.php...
[2019-09-09 12:41:37] Applying RenameClasses to _config.php...
[2019-09-09 12:41:37] Applying ClassToTraitRule to _config.php...
[2019-09-09 12:41:37] Applying RenameClasses to CmsEditLinkFieldTest.php...
[2019-09-09 12:41:37] Applying ClassToTraitRule to CmsEditLinkFieldTest.php...
modified:	src/Forms/Fields/CMSEditLinkField.php
@@ -2,8 +2,11 @@

 namespace Sunnysideup\CmsEditLinkField\Forms\Fields;

-use ReadonlyField;
-use Convert;
+
+
+use SilverStripe\Core\Convert;
+use SilverStripe\Forms\ReadonlyField;
+


 /**

modified:	src/Api/CMSEditLinkAPI.php
@@ -2,14 +2,25 @@

 namespace Sunnysideup\CmsEditLinkField\Api;

-use ViewableData;
-use Injector;
-use DataObject;
-use Member;
-use Controller;
-use Director;
-use Group;
-use ClassInfo;
+
+
+
+
+
+
+
+
+use SilverStripe\Core\Injector\Injector;
+use SilverStripe\ORM\DataObject;
+use SilverStripe\Security\Member;
+use SilverStripe\Control\Director;
+use SilverStripe\Control\Controller;
+use SilverStripe\Security\Group;
+use SilverStripe\Admin\ModelAdmin;
+use SilverStripe\Core\ClassInfo;
+use SilverStripe\Dev\TestOnly;
+use SilverStripe\View\ViewableData;
+



@@ -76,12 +87,12 @@
             $classFound = true;
         } else {
             $classFound = false;
-            foreach (ClassInfo::subclassesFor('ModelAdmin') as $i => $myAdminClassName) {
+            foreach (ClassInfo::subclassesFor(ModelAdmin::class) as $i => $myAdminClassName) {
                 for ($includeChildren = 0; $includeChildren < 2; $includeChildren++) {
-                    if ($myAdminClassName == 'ModelAdmin') {
+                    if ($myAdminClassName == ModelAdmin::class) {
                         continue;
                     }
-                    if (ClassInfo::classImplements($myAdminClassName, 'TestOnly')) {
+                    if (ClassInfo::classImplements($myAdminClassName, TestOnly::class)) {
                         continue;
                     }
                     $myModelAdminclassObject = Injector::inst()->get($myAdminClassName);

modified:	tests/CmsEditLinkFieldTest.php
@@ -1,4 +1,6 @@
 <?php
+
+use SilverStripe\Dev\SapphireTest;

 class CmsEditLinkFieldTest extends SapphireTest
 {

Writing changes for 3 files
✔✔✔