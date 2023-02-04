<?php

namespace Sunnysideup\CmsEditLinkField\Api;

use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Group;
use SilverStripe\Security\Member;

class CMSEditLinkAPI
{
    use Configurable;
    use Injectable;

    protected static $_cache = [];

    /**
     * model => link (e.g. my-model-admin).
     *
     * @var array
     */
    private static $overwrites = [];

    /**
     * common usage ...
     *
     *         public function CMSEditLink($action = null)
     *         {
     *             return CMSEditLinkAPI::find_edit_link_for_object($this, $action);
     *         };
     * returns an empty string if not found!
     *
     * @param DataObject|string $objectOrClassName
     * @param string            $action
     * @param string            $modelAdminURLOverwrite - predetermine modeladmin - e.g. SEGMENT as in
     *                                                  /admin/SEGMENT/Foo-Bar/EditForm/field/Foo-Bar/item/517/
     */
    public static function find_edit_link_for_object($objectOrClassName, ?string $action = '', ?string $modelAdminURLOverwrite = ''): string
    {
        $objectToEdit = self::get_data_object($objectOrClassName);

        if ($objectToEdit instanceof DataObject) {
            $id = $objectToEdit->exists() ? $objectOrClassName->ID : 0;
        } else {
            user_error('$objectOrClassName is not set correctly.', E_USER_NOTICE);

            return '';
        }

        if ($objectToEdit instanceof Member) {
            return Controller::join_links(
                Director::baseURL(),
                '/admin/security/EditForm/field/Members/item/' . $id . '/edit/'
            );
        }

        if ($objectToEdit instanceof Group) {
            return Controller::join_links(
                Director::baseURL(),
                '/admin/security/EditForm/field/Groups/item/' . $id . '/edit/'
            );
        }

        $overWrites = Config::inst()->get(self::class, 'overwrites');

        if (!$modelAdminURLOverwrite) {
            $modelAdminURLOverwrite = $overWrites[$objectOrClassName->ClassName] ?? '';
        }


        $MyModelAdminClassObject = null;
        $classFound = false;
        if ($modelAdminURLOverwrite) {
            $classFound = true;
        } else {
            $modelAdminResults = self::get_model_admin($objectToEdit->ClassName);
            if ([] !== $modelAdminResults) {
                $modelNameToEdit = $modelAdminResults['ModelNameToEdit'];
                $MyModelAdminClassObject = $modelAdminResults['MyModelAdminClassObject'];
                $classFound = true;
            }
        }

        if ($classFound) {
            $modelNameToEdit = self::sanitize_class_name($modelNameToEdit);
            if (0 === $id) {
                $id = 'new';
            }

            if (!$action) {
                $action = $modelNameToEdit . '/EditForm/field/' . $modelNameToEdit . '/item/' . $id . '/';
            }

            if ($modelAdminURLOverwrite) {
                $link = '/admin/' . $modelAdminURLOverwrite . '/' . $action;
            } elseif ($MyModelAdminClassObject) {
                if ($id) {
                    $link = $MyModelAdminClassObject->getCMSEditLinkForManagedDataObject($objectToEdit);
                } else {
                    $link = $MyModelAdminClassObject->Link($action);
                }
            } else {
                $link = '';
            }

            return Controller::join_links(
                Director::baseURL(),
                $link
            );
        }

        return '';
    }

    /**
     * @return DataObject|null
     */
    protected static function get_data_object($objectOrClassName)
    {
        $objectToEdit = null;
        if (is_string($objectOrClassName)) {
            $objectToEdit = Injector::inst()->get($objectOrClassName);
        } elseif ($objectOrClassName instanceof DataObject) {
            $objectToEdit = $objectOrClassName;
        }

        return $objectToEdit;
    }

    public static function get_model_admin(string $modelNameToEdit): array
    {
        $originalModelNameToEdit = $modelNameToEdit;
        if (!isset(self::$_cache[$originalModelNameToEdit])) {
            $classFound = false;
            self::$_cache[$originalModelNameToEdit] = [];
            $myAdminClassName = Config::inst()->get($modelNameToEdit, 'primary_model_admin_class');
            if ($myAdminClassName) {
                $classFound = true;
                $MyModelAdminClassObject = Injector::inst()->get($myAdminClassName);
            } else {
                self::$_cache[$originalModelNameToEdit] = [];
                $MyModelAdminClassObject = null;
                foreach (ClassInfo::subclassesFor(ModelAdmin::class) as $myAdminClassName) {
                    for ($includeChildren = 0; $includeChildren < 10; ++$includeChildren) {
                        if (ModelAdmin::class === $myAdminClassName) {
                            continue;
                        }

                        if (ClassInfo::classImplements($myAdminClassName, TestOnly::class)) {
                            continue;
                        }

                        $MyModelAdminClassObject = Injector::inst()->get($myAdminClassName);
                        $models = $MyModelAdminClassObject->getManagedModels();

                        foreach ($models as $model => $modelDetails) {
                            if (is_string($modelDetails)) {
                                $model = $modelDetails;
                            }

                            $childrenForModelBeingManaged = null;
                            if (0 !== $includeChildren) {
                                $childrenForModelBeingManaged = ClassInfo::subclassesFor($model);
                                if (is_array($childrenForModelBeingManaged)) {
                                    $modelsToSearch = array_reverse($childrenForModelBeingManaged);
                                }
                            } else {
                                $modelsToSearch = [$model];
                            }

                            foreach ($modelsToSearch as $modelToSearch) {
                                if ($modelToSearch === $modelNameToEdit) {
                                    if ($modelNameToEdit !== $model) {
                                        $modelNameToEdit = $model;
                                    }

                                    $classFound = true;

                                    break 4;
                                }
                            }
                        }
                    }
                }
            }

            if ($classFound && $modelNameToEdit && $MyModelAdminClassObject) {
                self::$_cache[$originalModelNameToEdit] = [
                    'ModelNameToEdit' => $modelNameToEdit,
                    'MyModelAdminClassObject' => $MyModelAdminClassObject,
                ];
            }
        }

        return self::$_cache[$originalModelNameToEdit];
    }

    protected static function sanitize_class_name(string $className): string
    {
        return str_replace('\\', '-', $className);
    }
}
