<?php

/**
 * Copyright (c) Jan Pospisil (http://www.jan-pospisil.cz)
 */

namespace SqlBuilder;

/**
 * IConnection
 * @author Jan Pospisil
 */

interface IConnection  {
	
	public function execute(Query $query);

}
