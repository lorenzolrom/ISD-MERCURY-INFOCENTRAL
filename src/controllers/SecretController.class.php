<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/04/2019
 * Time: 11:42 PM
 */


namespace controllers;


use business\SecretOperator;
use exceptions\EntryNotFoundException;
use models\HTTPRequest;
use models\HTTPResponse;

class SecretController extends Controller
{
    private const FIELDS = array('name', 'permissions');

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     * @throws \exceptions\ValidationError
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission('api-settings');

        $param = $this->request->next();

        if($this->request->method() === HTTPRequest::GET)
        {
            if($param === NULL)
                return $this->getAll();

            return $this->get($param);
        }
        else if($this->request->method() === HTTPRequest::POST)
        {
            return $this->issue();
        }
        else if($this->request->method() === HTTPRequest::PUT)
        {
            return $this->update($param);
        }
        else if($this->request->method() === HTTPRequest::DELETE)
        {
            return $this->delete($param);
        }

        return NULL;
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     */
    private function getAll(): HTTPResponse
    {
        $data = array();

        foreach(SecretOperator::getAll() as $secret)
        {
            $data[] = array(
                'id' => $secret->getId(),
                'name' => $secret->getName()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private function get(?string $param): HTTPResponse
    {
        $secret = SecretOperator::getSecretById((int) $param);

        return new HTTPResponse(HTTPResponse::OK, array(
            'id' => $secret->getId(),
            'name' => $secret->getName(),
            'secret' => $secret->getSecret(),
            'permissions' => $secret->getPermissions()
        ));
    }

    /**
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    private function issue(): HTTPResponse
    {
        $args = self::getFormattedBody(self::FIELDS);

        return new HTTPResponse(HTTPResponse::CREATED, SecretOperator::issue($args));
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    private function update(?string $param): HTTPResponse
    {
        $secret = SecretOperator::getSecretById((int) $param);
        $args = self::getFormattedBody(self::FIELDS);
        SecretOperator::update($secret, $args);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function delete(?string $param): HTTPResponse
    {
        $secret = SecretOperator::getSecretById((int) $param);
        SecretOperator::delete($secret);
        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }
}