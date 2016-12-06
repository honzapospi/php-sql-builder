<?php

/**
 * Copyright (c) Jan Pospisil (http://www.jan-pospisil.cz)
 */

namespace SqlBuilder;

/**
 * SqlBuilder
 * @author Jan Pospisil
 */

class Query extends \Nette\Object {

	const FETCH = 'fetch';
	const FETCH_ALL = 'fetchAll';

	private $select = array();
	private $leftJoin = array();
	private $where = array();
	private $from;
	private $fetchMode;
	private $fetchParams = array();
	private $order;
	private $limit;

	/**
	 * @param $select
	 * @return $this
	 */
	public function select($select){
		$this->select[] = $select;
		return $this;
	}

	/**
	 * @param $from
	 * @return $this
	 */
	public function from($from){
		$this->from = $from;
		return $this;
	}

	/**
	 * @param $table
	 * @param $on
	 * @return $this
	 */
	public function leftJoin($table, $on){
		$this->leftJoin[] = array(
			'table' => $table,
			'on' => $on
		);
		return $this;
	}

	/**
	 * @param $where
	 * @return $this
	 */
	public function where($where){
		if(is_array($where)){
			$this->where[] = $where;
			return $this;
		}
		$params = func_get_args();
		array_shift($params);
		$this->where[] = array(
			'where' => $where,
			'params' => $params
		);
		return $this;
	}

	public function fetch(){
		$this->fetchMode = self::FETCH;
		return $this;
	}

	public function fetchAll(){
		return $this->fetchMode = self::FETCH_ALL;
	}

	public function order($order){
		$this->order = $order;
		return $this;
	}

	public function limit($limit){
		$this->limit = $limit;
		return $this;
	}

	/****************************************************** SHORTERS   ************************************************/

	/**
	 * @param Query $query
	 */
	public function merge(Query $query){
		foreach($query->select as $select){
			$this->select($select);
		}
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
			return array(
				'method' => $this->fetchMode,
				'params' => $this->fetchParams
			);
		}
	}

	//**************************************************** PRIVATE ****************************************************/

	/**
	 * @return array
	 */
	public function buildQuery(){
		$params = array();
		$sql[] = 'SELECT '.implode(' ', $this->select);
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
		$return = array(implode(' ', $sql));
		foreach($params as $param)
			$return[] = $param;
		return $return;
	}

}
