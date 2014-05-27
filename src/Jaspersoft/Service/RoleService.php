<?php
namespace Jaspersoft\Service;

use Jaspersoft\Client\Client;
use Jaspersoft\Tool\Util;
use Jaspersoft\Dto\Role\Role;

class RoleService
{
	protected $service;
	protected $restUrl2;

    public function __construct(Client &$client)
    {
        $this->service = $client->getService();
        $this->restUrl2 = $client->getURL();
    }
	
	private function makeUrl($organization = null, $roleName = null, $params = null)
    {
        if(!empty($organization))
            $url = $this->restUrl2 . '/organizations/' . $organization . '/roles';
        else
            $url = $this->restUrl2 . '/roles';
        if (!empty($roleName))
            $url .= '/' . $roleName;
        // If a role name is defined, no parameters are expected
        else if (!empty($params))
            $url .= '?' . Util::query_suffix($params);
        return $url;
    }

    /**
     * Search for many or all roles on the server.
     * You can search by organization as well.
     *
     * @param $organization string the organizations to search for roles within
     * @param $includeSubOrgs boolean return roles of suborganizations
     * @param $user array retrieves the roles of specific user(s) in the array, users must be defined as username|organization if multitenancy is enabled (pro)
     * @param $hasAllUsers boolean return the intersection of roles defined on all users in $user
     * @param $q string A query string to filter results by
     * @param $limit int Limit the amount of results (pagination controls)
     * @param $offset int Begin search results at this offset (pagination controls)
     * @return array
     * @throws \Jaspersoft\Exception\RESTRequestException
     */
    public function searchRoles($organization = null, $includeSubOrgs = null, $user = null, $hasAllUsers = false, $q = null, $limit = 0, $offset = 0)
    {
        $result = array();
        $url = self::makeUrl($organization, null, compact('includeSubOrgs', 'user', 'hasAllUsers', 'q', 'limit', 'offset'));
        $data = $this->service->prepAndSend($url, array(200, 204), 'GET', null, true, 'application/json', 'application/json');
        $data = (!empty($data)) ? json_decode($data, true) : null;
        if ($data === null)
            return $result;
        foreach ($data['role'] as $r)
            $result[] = @new Role($r['name'], $r['tenantId'], $r['externallyDefined']);
        return $result;
    }
	
    /** Get a Role by its name
     *
     * @param $roleName
     * @param $organization - Name of organization role belongs to
     * @return Role
     * @throws \Jaspersoft\Exception\RESTRequestException
     */
    public function getRole($roleName, $organization = null)
    {
        $url = self::makeUrl($organization, $roleName);
        $resp = $this->service->prepAndSend($url, array(200), 'GET', null, true, 'application/json', 'application/json');	
		$data = json_decode($resp);
        return @new Role($data->name, $data->tenantId, $data->externallyDefined);
    }
	
    /**
     * Add a new role.
     *
     * Provide a role object that represents the role you wish to add.
     *
     * @param Role $role - role to add (1 at a time)
     * @throws \Jaspersoft\Exception\RESTRequestException
     */
    public function createRole(Role $role)
    {
        $url = self::makeUrl($role->tenantId, $role->name);
        $this->service->prepAndSend($url, array(201, 200), 'PUT', json_encode($role), false, 'application/json', 'application/json');
    }
	
    /**
     * Remove a role currently in existence.
     *
     * Provide the Role object of the role you wish to remove. Use getRole() to retrieve Roles.
     *
     * @param Role $role
     * @throws \Jaspersoft\Exception\RESTRequestException
     */
	public function deleteRole(Role $role)
    {
        $url = self::makeUrl($role->tenantId, $role->name);
        $this->service->prepAndSend($url, array(204), 'DELETE', null, false);
	}
	
    /**
     * Update a role currently in existence.
     *
     * Provide the Role object of the role you wish to change, then a string of the new name
     * you wish to give the role. You can optionally provide a new tenantId if you wish to change
     * that as well.
     *
     * @param Role $role - Role object to be changed
     * @param string $oldName - previous name for the role
     * @throws \Jaspersoft\Exception\RESTRequestException
     */
    public function updateRole(Role $role, $oldName = null)
    {
        $url = self::makeUrl($role->tenantId, $oldName);
        $this->service->prepAndSend($url, array(200, 201), 'PUT', json_encode($role), false, 'application/json', 'application/json');
    }
	
}
