<?php

use Doctrine\ORM\QueryBuilder;
use DoctrineExtensions\Paginate\Paginate;
use Doctrine\ORM\Query;
use Slim\Slim;

/**
 * Slim Datatable Wrapper
 * 
 * This class is used to handle server-side works of jquery DataTables Jquery 
 * Plugin(http://datatables.net) and Doctrine QueryBuilder
 * @author Egi Hasdi <egi.hasdi@sangkuriang.co.id>
 */
class DataTables implements JsonSerializable {

    public $totalRecords;
    public $totalDisplayRecords;
    public $draw;
    public $data = array();

    /**
     * Create an instance of DataTables class
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param Array $params Input parameters
     */
    public function __construct(QueryBuilder $queryBuilder, $view = null, $searchCols = array(), $data = array()) {

        $app = Slim::getInstance();

        // Current request object
        $request = $app->request;

        // datatable sEcho request parameter
        $this->draw = $request->get('draw');

        // count total records of queryBuilder
        $this->totalRecords = $this->count($queryBuilder);

        $search = $request->get('search');

//        perform filtering and sorting
        if ($search['value'] && !empty($searchCols)) {
            $conditions = array();
            foreach ($searchCols as $index => $col)
                $conditions[] = $queryBuilder->expr()->like($col, ":search");
            $where = call_user_method_array('orX', $queryBuilder->expr(), $conditions);
            $queryBuilder->andWhere($where);
            $queryBuilder->setParameter("search", "%" . $search['value'] . "%");
        }

        // count records after filtering
        $this->totalDisplayRecords = $this->count($queryBuilder);

        // perform pagination
        $queryBuilder->setFirstResult($request->get('start'));
        $queryBuilder->setMaxResults($request->get('length'));

        $this->data = $queryBuilder->getQuery()->getResult();

        // perform parsing
        if (null !== $view && !empty($this->data)) {

            // render the twig datatable template as string
            $str = $app->view->render($view, array_merge($data, array("data" => $this->data)));

            // parse the string into xml
            $xml = simplexml_load_string($str, null, LIBXML_NOCDATA);

            // convert it to json!
            $json = str_replace(':{}', ':null', json_encode($xml));

            // convert the json into array!
            $data = json_decode($json, true);

            // finally set the result data
            $this->data = $xml->count() > 1 ? $data['aaData'] : array($data['aaData']);
        }
    }

    private function count(QueryBuilder $queryBuilder) {
        return Paginate::getTotalQueryResults($queryBuilder->getQuery());
    }

    public function jsonSerialize() {
        return array(
            "recordsTotal" => $this->totalRecords,
            "recordsFiltered" => $this->totalDisplayRecords,
            "draw" => $this->draw,
            "data" => $this->data
        );
    }

}
