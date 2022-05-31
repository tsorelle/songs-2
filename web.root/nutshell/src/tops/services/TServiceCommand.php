<?php
/**
 * Created by PhpStorm.
 * User: tsorelle
 * Date: 2/25/14
 * Time: 3:13 PM
 */

namespace Tops\services;
use Tops\sys\IUser;
use Tops\sys\TL;
use Tops\sys\TLanguage;
use Tops\sys\TPermissionsManager;
use Tops\sys\TSession;
// use Tops\sys\IUser;
//  use Tops\sys\TTracer;
// use Tops\sys\TUser;
use Tops\sys\TTracer;
use Tops\sys\TUser;

/**
 * Class TServiceCommand
 * @package Tops\services
 */
abstract class TServiceCommand {
    /**
     * @var TServiceContext
     */
    private  $context;
    private  $request;

    /**
     * @var array
     */
    private $authorizations = array();
    private $authorizedRoles = array();
    private $errorCount = 0;

    abstract protected function run();

    protected function hasErrors() {
        return ($this->errorCount > 0);
    }

    protected function addErrorMessage($text,$translated = false) {
        $this->context->AddErrorMessage($text,$translated);
        $this->errorCount++;
    }

    public function addInfoMessage($text,$translated = false) {
        $this->context->AddInfoMessage($text,$translated);
    }

    public function addWarningMessage($text,$translated = false) {
        $this->context->AddWarningMessage($text,$translated);
    }

    public function setError($text,$returnErrorMsg=true) {
        $this->context->setError();
        $this->errorCount++;
        if ($returnErrorMsg) {
            $this->setReturnValue(TLanguage::text($text));
        }
        return;
    }

    public function setReturnValue($value) {
        $this->context->SetReturnValue($value);
    }

    /**
     * Get return value for unit testing
     * @return mixed|null
     */
    private function getReturnValue() {
        $response = $this->context->GetResponse();
        return ($response == null) ? null : $response->Value;
    }

    public function runTest($request = null)
    {
        $this->context = new TServiceContext();
        $this->setRequest($request);
        $this->run();
        return $this->context->GetResponse();
        // return $this->getReturnValue();
    }

    public function getTestResponse($request) {
        $this->context = new TServiceContext();
        $this->setRequest($request);
        $this->run();
        return $this->context->GetResponse();
    }

    /**
     * Set request for unit testing.
     * @param $value
     */
    public function setRequest($value) {
        $this->request = $value;
    }


    public function getRequest() {

        return $this->request;
    }


    /**
     * @return IMessageContainer
     */
    protected function  getMessages()
    {
        return $this->context;
    }

    /**
     * @var IUser
     */
    private $user;

    /**
     * @return IUser
     */
    protected function getUser() {
        if (!isset($this->user)) {
            $this->user = TUser::getCurrent();
        }
        return $this->user;
    }


    public function isAuthorized() {

        if (empty($this->authorizations && empty($this->authorizedRoles))) {
            return true;
        }
        /**
         * @var IUser $user
         */
        $user = $this->getUser();
        if ($user->isAdmin()) {
            return true;
        }
        if (in_array(TPermissionsManager::authenticatedRole,$this->authorizedRoles) && $user->isAuthenticated()) {
            return true;
        }
        foreach($this->authorizedRoles as $role) {
            if ($user->isMemberOf($role)) {
                return true;
            }
        }
        foreach($this->authorizations as $auth) {
            if ($user->isAuthorized($auth)) {
                return true;
            }
        }

        $this->addErrorMessage($user->isAuthenticated() ? 'service-no-auth' : 'service-no-guest');
        return false;
    }

    /**
     * @param $authorization
     *
     * Use in constructor.  Example:
     *     public function __construct() {
     *         $this->addAuthorization("administer registrations");
     *     }
     */
    protected function addAuthorization($authorization)
    {
        if (!in_array($authorization, $this->authorizations)) {
            array_push($this->authorizations, $authorization);
        }
    }

    protected function addAuthorizedRole($roleName) {
        if (!in_array($roleName, $this->authorizedRoles)) {
            array_push($this->authorizedRoles, $roleName);
        }

    }

    protected function requireAuthentication() {
        $this->addAuthorizedRole(TPermissionsManager::authenticatedRole);
    }

    /**
     * Check service request for insecure content, to prevent cross site scripting attacks.
     * The method can be overriden in sub-classes in order to either disable or extend
     * the security checking.
     *
     * The default functionality checks only for specific tags. It may be necessary to perform
     * more rigorous case specific checking in the sub-class.
     *
     * Returns true if no potentially unsave content is found.
     *
     * @param $request  mixed
     * @return bool
     */
    protected function secureContent($request)
    {
        if (empty($request)) {
            return true;
        }

        if (is_object($request)) {
            $content = json_encode($request);
        }
        else {
            $content = $request;
            if (is_numeric(trim($content))) {
                return true;
            }
        }

        if (strpos($content,'<') !== false) {
            $tags = $this->getUser()->isAdmin() ?
                ['script','object'] :
                ['script','object','form','style','iframe'];

            $testtext = strtolower(str_replace([" ","\n","\t","\r","\\n","\\t","\\r"],'',$content));
            foreach ($tags as $tag) {
                if (strpos($testtext,'<'.$tag) !== false) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @param $request
     * @return TServiceResponse
     */
    public function execute($request, $securityToken = null) {
        $this->context = new TServiceContext();
        if (TSession::AuthenitcateSecurityToken($securityToken)) {
            if ($this->secureContent($request)) {
                $this->request = $request;
                if ($this->isAuthorized()) {
                    $this->run();
                }
            }
            else {
                $this->addErrorMessage("service-insecure");
            }
        }
        else {
            $this->addErrorMessage("session-expired");
        }

        $response = $this->context->GetResponse();
        if (TTracer::Enabled()) {
            TTracer::Stop();
            $response->TraceMessages = TTracer::GetMesssageAsString();
        }
        return $this->context->GetResponse();
    }
}
