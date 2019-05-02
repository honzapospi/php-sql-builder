<?php

/**
 * Copyright (c) Jan Pospisil (http://www.jan-pospisil.cz)
 */

declare(strict_types=1);

namespace SqlBuilder;
use Nette\SmartObject;

/**
 * SqlBuilder
 * @author Jan Pospisil
 */

class Query {
	use SmartObject;

	const FETCH = 'fetch';
	const FETCH_ALL = 'fetchAll';

	private $select = ['*'];
	private $leftJoin = [];
	private $where = [];
	private $from;
	private $fetchMode;
	private $fetchParams = [];
	private $order;
	private $limit;
	private $offset;


	/**
	 * @param $select
	 * @return $this
	 */
	public function select(array $select): Query {
		$this->select = $select;
		return $this;
	}

	/**
	 * @param $from
	 * @return $this
	 */
	public function from(string $from): Query {
		$this->from = $from;
		return $this;
	}

	/**
	 * @param $table
	 * @param $on
	 * @return $this
	 */
	public function leftJoin(string $table, string $on){
		$this->leftJoin[] = [
			'table' => $table,
			'on' => $on
		];
		return $this;
	}

	/**
	 * @param $where
	 * @return $this
	 */
	public function where($where): Query{
		if(is_array($where)){
			$this->where[] = $where;
			return $this;
		}
		$params = func_get_args();
		array_shift($params);
		$this->where[] = [
			'where' => $where,
			'params' => $params
		];
		return $this;
	}

	public function fetch(): Query {
		$this->fetchMode = self::FETCH;
		return $this;
	}

	public function fetchAll(): Query {
		$this->fetchMode = self::FETCH_ALL;
		return $this;
	}

	public function order(string $order): Query {
		$this->order = $order;
		return $this;
	}

	public function limit(int $limit, int $offset = null): Query {
		$this->limit = $limit;
		$this->offset = $offset;
		return $this;
	}

	public function page(int $page, int $itemsPerPage){
		if($page < 1){
			$itemsPerPage = 0;
		}
		return $this->limit($itemsPerPage, ($page - 1) * $itemsPerPage);
	}

	/****************************************************** SHORTERS   ************************************************/

	/**
	 * @param Query $query
	 */
	public function merge(Query $query){
		$this->select($query->getSelect());
		foreach($query->leftJoins as $join){
			$this->leftJoin($join['table'], $join['on']);
		}
		foreach($query->where as $where){
			$this->where($where);
		}
	}

	//*************************************************** GETTERS ****************************************************//

	/**
	 * @return array
	 */
	public function getSelect(){
		return $this->select;
	}

	/**
	 * @return array
	 */
	public function getLeftJoins(){
		return $this->leftJoin;
	}

	/**
	 * @return array
	 */
	public function getWhere(){
		return $this->where;
	}

	public function getFetch(){
		if($this->fetchMode){
			return [
				'method' => $this->fetchMode,
				'params' => $this->fetchParams
			];
		}
	}

	//**************************************************** PRIVATE ****************************************************/

	/**
	 * @return array
	 */
	public function buildQuery(){
		$params = [];
		$select = [];
		foreach($this->select as $key => $val)
			$select[] = is_int($key) ? $val : $key.' as '.$val;
		$sql[] = 'SELECT '.implode(', ', $select);
		$sql[] = 'FROM '.$this->from;
		foreach($this->leftJoin as $join){
			$sql[] = 'LEFT JOIN '.$join['table'].' ON '.$join['on'];
		}
		if($this->where){
			foreach($this->where as $where){
				$w[] = $where['where'];
				foreach($where['params'] as $param)
					$params[] = $param;
			}
			$sql[] = 'WHERE';
			$sql[] = implode(' AND ', $w);
		}
		if($this->order)
			$sql[] = 'ORDER BY '.$this->order;
		if($this->limit)
			$sql[] = 'LIMIT '.$this->limit;
		if($this->limit && $this->offset)
			$sql[] = 'OFFSET '.$this->offset;
		$return = [implode(' ', $sql)];
		foreach($params as $param)
			$return[] = $param;
		return $return;
	}

}
