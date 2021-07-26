<?php
/**
 * Created by PhpStorm.
 * User: pongs
 * Date: 4/4/2018
 * Time: 12:20 PM
 */

namespace App\Traits;

use App\Enums\PermissionKey;

trait PermissionChecker
{
    public $permission = [];
    public $auth_user;

    public function buildPermission($moduleName) {
        $_moduleName = str_replace('_', '-', $moduleName);
        $this->permission = array(
            PermissionKey::access()->value => 'access-'.$_moduleName,
            PermissionKey::list()->value => 'list-'.$_moduleName,
            PermissionKey::view()->value => 'view-'.$_moduleName,
            PermissionKey::create()->value => 'create-'.$_moduleName,
            PermissionKey::edit()->value => 'edit-'.$_moduleName,
            PermissionKey::delete()->value => 'delete-'.$_moduleName);
    }
    /**
     * Check any permission on the authentication user
     * @param string $permissionValue
     * @return bool
     */
    public function checkPermission($permissionValue) {
        $this->auth_user = getAuthUser();

        if(!$this->auth_user || !$permissionValue || !$this->auth_user->can($permissionValue)) {
            return false;
        }

        return true;
    }

    public function can($permissionValue) {
        return $this->checkPermission($permissionValue);
    }

    public function isAuth() {
        $this->auth_user = getAuthUser();
        if(!$this->auth_user) {
            return false;
        }
        return true;
    }
    public function cannotViewOrList() {
        if (!$this->canAccess()
            && !$this->canList()
            && !$this->canView()) {
            return true;
        }
        return false;
    }

    public function canView() {
        if (!array_key_exists(PermissionKey::view()->value, $this->permission))
            return $this->checkThis(PermissionKey::list()->value);

        return $this->checkThis(PermissionKey::view()->value);
    }

    public function canCreate() {
        return $this->checkThis(PermissionKey::create()->value);
    }

    public function canList() {
        return $this->checkThis(PermissionKey::list()->value);
    }

    public function canAccess() {
        return $this->checkThis(PermissionKey::access()->value);
    }

    public function canEdit() {
        return $this->checkThis(PermissionKey::edit()->value);
    }

    public function canDelete() {
        return $this->checkThis(PermissionKey::delete()->value);
    }

    public function getCurrentUser() {
        if (!$this->auth_user) {
            $this->auth_user = getAuthUser();
        }
        return $this->auth_user;
    }

    public function getCurrentDepartmentId() {
        $user = $this->getCurrentUser();
        if (!$user || !$user->Profile) {
            return null;
        }

        return $user->Profile->department_id;
    }

    /**
     * Base on general permission key: access, list, create, edit, delete
     * @param string $key
     * @return bool
     */
    private function checkThis($key) {
        $this->auth_user = getAuthUser();

        if(!$this->auth_user || sizeof($this->permission) == 0 || !array_key_exists($key, $this->permission)) {
            return false;
        }

        $permissionCode = $this->permission[$key];
        if (!$permissionCode || !$this->auth_user->can($permissionCode)) {
            return false;
        }

        return true;
    }

    protected function basicPermissions() {
        $check = array();
        $check['access'] = $this->canAccess() ? 1: 0;
        $check['list'] = $this->canList() ? 1: 0;
        $check['view'] = $this->canView() ? 1: 0;
        $check['create'] = $this->canCreate() ? 1: 0;
        $check['edit'] = $this->canEdit() ? 1: 0;
        $check['delete'] = $this->canDelete() ? 1: 0;
        return collect($check);
    }

    /**
     * See in PermissionChecker, API to use from method: listCurrentPermissions
     * @return array|null
     */
    protected function listCurrentPermissions() {
        //Log::debug("-----: on listing permissions - " . $this->module);
        $data = $this->basicPermissions();
        return $this->success(compact('data'));
    }

    public function listPermissionKeys() {
        return $this->permission;
    }
}