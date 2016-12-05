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

	private $select = array();
	private $leftJoin = array();
	private $where = array();
	private $from;

	public function select($select){
		$this->select[] = $select;
		return $this;
	}

	public function from($from){
		$this->from = $from;
		return $this;
	}

	public function leftJoin($table, $on){
		$this->leftJoin[] = array(
			'table' => $table,
			'on' => $on
		);
		return $this;
	}

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

	public function execute(IConnection $context){
		return call_user_func_array(array($context, 'query'), $this->buildQuery());
	}

	private function buildQuery(){
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

		$return = array(implode(' ', $sql));
		foreach($params as $param)
			$return[] = $param;
		return $return;
	}

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

	public function getSelect(){
		return $this->select;
	}

	public function getLeftJoins(){
		return $this->leftJoin;
	}

	public function getWhere(){
		return $this->where;
	}
}
