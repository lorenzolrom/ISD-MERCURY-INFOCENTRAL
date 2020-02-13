<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 2/12/2020
 * Time: 4:36 PM
 */


namespace extensions\trs\controllers;


use controllers\Controller;
use exceptions\EntryInUseException;
use exceptions\EntryNotFoundException;
use exceptions\ValidationError;
use models\HTTPResponse;

class CommodityController extends Controller
{

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws ValidationError
     * @throws EntryNotFoundException
     * @throws EntryInUseException
     */
    public function getResponse(): ?HTTPResponse
    {
        $p1 = $this->request->next(); // 1st URI part

        if($p1 === 'categories') // All category requests handled by that controller
        {
            $cc = new CommodityCategoryController($this->request);
            return $cc->getResponse();
        }

        return NULL;
    }
}