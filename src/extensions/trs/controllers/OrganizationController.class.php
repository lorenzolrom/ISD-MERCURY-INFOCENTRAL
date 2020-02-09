<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 2/08/2020
 * Time: 3:35 PM
 */


namespace extensions\trs\controllers;


use controllers\Controller;
use extensions\trs\commands\GetOrganizationCommand;
use extensions\trs\commands\SearchOrganizationsCommand;
use models\HTTPRequest;
use models\HTTPResponse;

class OrganizationController extends Controller
{

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\MercuryException
     * @throws \exceptions\SecurityException
     */
    public function getResponse(): ?HTTPResponse
    {
        $p1 = $this->request->next(); // First parameter in URL

        if($this->request->method() === HTTPRequest::GET)
        {
            if($p1 === NULL)
                return $this->searchOrganizations();
            else
                return $this->getOrganization($p1);
        }
        else if($this->request->method() === HTTPRequest::POST)
        {
            if($p1 === 'search')
                return $this->searchOrganizations();
        }

        return NULL;
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function searchOrganizations(): HTTPResponse
    {
        $search = new SearchOrganizationsCommand(self::getFormattedBody(SearchOrganizationsCommand::PARAMS, FALSE));
        $search->execute();

        $results = array();

        foreach($search->getResult() as $organization)
        {
            $results[] = $organization->toArray();
        }

        return new HTTPResponse(HTTPResponse::OK, $results);
    }

    /**
     * @param $id
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\MercuryException
     * @throws \exceptions\SecurityException
     */
    private function getOrganization($id): HTTPResponse
    {
        $get = new GetOrganizationCommand((int)$id);
        if($get->execute())
        {
            return new HTTPResponse(HTTPResponse::OK, $get->getResult()->toArray());
        }
        else
        {
            throw $get->getError();
        }
    }
}