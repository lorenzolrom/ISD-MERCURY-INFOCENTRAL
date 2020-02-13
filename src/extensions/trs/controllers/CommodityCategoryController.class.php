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
use extensions\trs\commands\CreateCommodityCategoryCommand;
use extensions\trs\commands\DeleteCommodityCategoryCommand;
use extensions\trs\commands\GetCommodityCategoryCommand;
use extensions\trs\commands\SearchCommodityCategoriesCommand;
use extensions\trs\commands\UpdateCommodityCategoryCommand;
use extensions\trs\models\CommodityCategory;
use models\HTTPRequest;
use models\HTTPResponse;

class CommodityCategoryController extends Controller
{

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\MercuryException
     */
    public function getResponse(): ?HTTPResponse
    {
        $p1 = $this->request->next();
        $p2 = $this->request->next();

        if($this->request->method() === HTTPRequest::GET)
        {
            if($p1 === NULL)
                return $this->searchCategories();
            else if ($p1 !== NULL AND $p2 === NULL)
                return $this->getCategory($p1);
        }
        else if($this->request->method() === HTTPRequest::POST)
        {
            if($p1 === 'search' AND $p2 === NULL)
                return $this->searchCategories();
            else if($p1 === NULL)
                return $this->createCategory();
        }
        else if($this->request->method() === HTTPRequest::PUT)
        {
            if($p1 !== NULL AND $p2 === NULL)
                return $this->updateCategory($p1);
        }
        else if($this->request->method() === HTTPRequest::DELETE)
        {
            if($p1 !== NULL AND $p2 === NULL)
                return $this->deleteCategory($p1);
        }

        return NULL;
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function searchCategories(): HTTPResponse
    {
        $search = new SearchCommodityCategoriesCommand(self::getFormattedBody(SearchCommodityCategoriesCommand::FIELDS));
        $search->execute();

        $data = array();

        foreach($search->getResult() as $category)
        {
            $data[] = array(
                'id' => $category->getId(),
                'name' => $category->getName()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @param $id
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\MercuryException
     * @throws \exceptions\SecurityException
     */
    private function getCategory($id): HTTPResponse
    {
        $get = new GetCommodityCategoryCommand((int)$id);

        if(!$get->execute())
        {
            throw $get->getError();
        }

        $cat = $get->getResult();

        return new HTTPResponse(HTTPResponse::OK, array(
            'id' => $cat->getId(),
            'name' => $cat->getName()
        ));

    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\MercuryException
     * @throws \exceptions\SecurityException
     */
    private function createCategory(): HTTPResponse
    {
        $create = new CreateCommodityCategoryCommand(self::getFormattedBody(CommodityCategory::FIELDS, TRUE));

        if(!$create->execute())
            throw $create->getError();

        $cat = $create->getResult();

        return new HTTPResponse(HTTPResponse::CREATED, array('id' => $cat->getId()));
    }

    /**
     * @param $id
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\MercuryException
     * @throws \exceptions\SecurityException
     */
    private function updateCategory($id): HTTPResponse
    {
        $get = new GetCommodityCategoryCommand((int)$id);

        if(!$get->execute())
            throw $get->getError();

        $cc = $get->getResult();

        $update = new UpdateCommodityCategoryCommand($cc, self::getFormattedBody(CommodityCategory::FIELDS, TRUE));

        if(!$update->execute())
            throw $update->getError();

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param $id
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\MercuryException
     * @throws \exceptions\SecurityException
     */
    private function deleteCategory($id): HTTPResponse
    {
        $get = new GetCommodityCategoryCommand((int)$id);

        if(!$get->execute())
            throw $get->getError();

        $cc = $get->getResult();

        $delete = new DeleteCommodityCategoryCommand($cc);

        if(!$delete->execute())
            throw $delete->getError();

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }
}