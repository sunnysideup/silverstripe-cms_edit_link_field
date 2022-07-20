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
     * @var [type]
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
        $overWrites = Config::inst()->get(self::class, 'overwrites');
        if (is_string($objectOrClassName)) {
            $modelNameToEdit = $objectOrClassName;
            $objectToEdit = Injector::inst()->get($modelNameToEdit);
            $id = 0;
        } else {
            $modelNameToEdit = $objectOrClassName->ClassName;
            $objectToEdit = $objectOrClassName;
        }

        if (! $modelAdminURLOverwrite) {
            $modelAdminURLOverwrite = $overWrites[$objectOrClassName->ClassName] ?? '';
        }

        if ($objectToEdit instanceof DataObject) {
            $id = $objectToEdit->exists() ? $objectOrClassName->ID : 0;
        } else {
            user_error('$objectOrClassName is not set correctly.', E_USER_NOTICE);

            return '';
        }

        if ($objectToEdit instanceof Member) {
            return Controller::join_links(
                Director::baseURL(),
                '/admin/security/EditForm/field/Members/item/' . $objectToEdit->ID . '/edit/'
            );
        }

        if ($objectToEdit instanceof Group) {
            return Controller::join_links(
                Director::baseURL(),
                '/admin/security/EditForm/field/Groups/item/' . $objectToEdit->ID . '/edit/'
            );
        }

        $myModelAdminclassObject = null;
        $classFound = false;
        if ($modelAdminURLOverwrite) {
            $classFound = true;
        } else {
            $modelAdminResults = self::get_model_admin($modelNameToEdit);
            if ([] !== $modelAdminResults) {
                $modelNameToEdit = $modelAdminResults['ModelNameToEdit'];
                $myModelAdminclassObject = $modelAdminResults['MyModelAdminclassObject'];
                $classFound = true;
            }
        }

        if ($classFound) {
            $modelNameToEdit = self::sanitize_class_name($modelNameToEdit);
            if (0 === $id) {
                $id = 'new';
            }

            if (! $action) {
                $action = $modelNameToEdit . '/EditForm/field/' . $modelNameToEdit . '/item/' . $id . '/';
            }

            if ($modelAdminURLOverwrite) {
                $link = '/admin/' . $modelAdminURLOverwrite . '/' . $action;
            } elseif ($myModelAdminclassObject) {
                $link = $myModelAdminclassObject->Link($action);
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

    public static function get_model_admin(string $modelNameToEdit): array
    {
        $originalModelNameToEdit = $modelNameToEdit;
        if (! isset(self::$_cache[$originalModelNameToEdit])) {
            $classFound = false;
            self::$_cache[$originalModelNameToEdit] = [];
            $myAdminClassName = Config::inst()->get($modelNameToEdit, 'primary_model_admin_class');
            if ($myAdminClassName) {
                $classFound = true;
                $myModelAdminclassObject = Injector::inst()->get($myAdminClassName);
            } else {
                self::$_cache[$originalModelNameToEdit] = [];
                $myModelAdminclassObject = null;
                foreach (ClassInfo::subclassesFor(ModelAdmin::class) as $myAdminClassName) {
                    for ($includeChildren = 0; $includeChildren < 10; ++$includeChildren) {
                        if (ModelAdmin::class === $myAdminClassName) {
                            continue;
                        }

                        if (ClassInfo::classImplements($myAdminClassName, TestOnly::class)) {
                            continue;
                        }

                        $myModelAdminclassObject = Injector::inst()->get($myAdminClassName);
                        $models = $myModelAdminclassObject->getManagedModels();

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

            if ($classFound && $modelNameToEdit && $myModelAdminclassObject) {
                self::$_cache[$originalModelNameToEdit] = [
                    'ModelNameToEdit' => $modelNameToEdit,
                    'MyModelAdminclassObject' => $myModelAdminclassObject,
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
