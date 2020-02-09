<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 2/08/2020
 * Time: 2:34 PM
 */


namespace extensions\trs\controllers;


use controllers\Controller;
use models\HTTPResponse;

/**
 * Class TRSController
 *
 * Hub for all TRS routes
 *
 * @package extensions\trs\controllers
 */
class TRSController extends Controller
{

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public function getResponse(): ?HTTPResponse
    {
        $subject = $this->request->next();

        if($subject === 'organizations')
        {
            $oc = new OrganizationController($this->request);
            return $oc->getResponse();
        }

        return NULL;
    }
}