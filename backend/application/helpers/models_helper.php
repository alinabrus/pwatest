<?php

class StatPeriod {
	const DEFAULT_PERIOD = 90;

	protected $startDate;
	protected $endDate;

	public function __construct($startDate = null, $endDate = null) {
		if (empty($startDate) && empty($endDate)) {
			$this->initByDaysFromNow(self::DEFAULT_PERIOD);
		}
		else {
			if (strtotime($startDate) > strtotime($endDate))
				$startDate = date("Y-m-d", strtotime($endDate) - (86400 * self::DEFAULT_PERIOD));
			$this->startDate = $startDate;
			$this->endDate = $endDate;
		}
	}

	public function initByDaysFromNow($days){
		$endTime = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
    	$startTime = $endTime - (86400 * $days);
        $this->startDate = date('Y-m-d', $startTime);
        $this->endDate = date('Y-m-d');
	}

	public function __get($property) {
        if (isset($this->$property)) {
            return $this->$property;
        }
        return null;
    }

	public function getPreviousPeriod() {

	}

	/**
	* Converts date range into sql query string
	*
	* @param string $dbColumnName - Column name used in sql - required
	* @param boolean $previousPeriodFlag - show info for previous period (?) - default false
	* @param boolean $addTime - Add time to sql expression - default false
	*
	* @return sql string for date condition
	*/

	public function getSqlExpression($dbColumnName, $previousPeriodFlag = false, $addTime = false) {
		$start = 'str_to_date("'.$this->startDate.'","%Y-%m-%d")';
   		$end = 'str_to_date("'.$this->endDate.'","%Y-%m-%d")';

    	if ($addTime) {
			$this->startDate = $this->startDate.' 00:00:00';
			$this->endDate = $this->endDate.' 23:59:59';
			$start = 'str_to_date("'.$this->startDate.'","%Y-%m-%d %H:%i:%s")';
    		$end = 'str_to_date("'.$this->endDate.'","%Y-%m-%d %H:%i:%s")';
		}

    	if ( ! $previousPeriodFlag) {
    		$dateCondition = $dbColumnName.' between '.$start.' and '.$end;
    	}
    	else {
   			$dateCondition = $dbColumnName.' between date_sub('.$start.',INTERVAL (1 + datediff('.$end.','.$start.') ) DAY)
										     and date_sub('.$start.', INTERVAL 1 DAY)';
    	}
    	return $dateCondition;
	}

        public function getStatPeriodArray() {
			$period = array($this->startDate,$this->endDate);
			return $period;
		}

		public function asString($format = '%d/%m/%Y', $delimiter = ' - ') {
			if ($this->startDate == $this->endDate) return strftime($format, strtotime($this->startDate));
			else return strftime($format, strtotime($this->startDate)).$delimiter.strftime($format, strtotime($this->endDate));
		}
}

class StatPeriodList {
	protected $items = array();

	public function __construct($statPeriodItems = null){
		if (is_array($statPeriodItems)) $this->items = $statPeriodItems;
	}

	public function addItem($statPeriod){
		if (is_a($statPeriod,'StatPeriod')) $this->items[] = $statPeriod;
	}

	public function getSqlExpression($dbColumnName, $previousPeriodFlag = false, $useTime = false) {
		if (empty($this->items)) return '';
		$sqlItems = array();
		foreach ($this->items as $item) {
			$sqlItems[] = $item->getSqlExpression($dbColumnName, $previousPeriodFlag, $useTime);
		}
		return '('.implode(') or (',$sqlItems).')';
	}
}

class FilterItem 
{
	protected $filterName;
	protected $filterField;
	protected $filterTableAlias;
	protected $filterValue;
	protected $filterOption;
	protected $filterValueUnfiltered;
	
	const OPTION_EQUALS = 'equal';
	const OPTION_CONTAINS = 'contains';
	const OPTION_STARTS = 'starts';
	const OPTION_ENDS = 'ends';
	
	public static $supportedOptions = array(self::OPTION_EQUALS, self::OPTION_CONTAINS, self::OPTION_STARTS, self::OPTION_ENDS);
	
	public function __construct($filterTableAlias, $filterField, $filterName = null, $filterValue = null, $filterOption = self::OPTION_EQUALS, $filterValueUnfiltered = null){
		$this->filterField = $filterField;
		$this->filterName = empty($filterName) ? $filterField : $filterName;
		$this->filterTableAlias = $filterTableAlias;
		$this->setFilterValue($filterValue, $filterOption);
		$this->filterValueUnfiltered = $filterValueUnfiltered;
	}
	
	public function setFilterValue($filterValue = null, $filterOption = self::OPTION_EQUALS){
		if ($filterValue === $this->filterValueUnfiltered) 
			$filterValue = null;
		$this->filterValue = $filterValue;
		$this->filterOption = $filterOption;
	}
	
	public function __get($property) {
        if (isset($this->$property)) {
            return $this->$property;
        }
		return null;
    }
}

class Filter 
{
	protected $availableFilters;
	protected $unfilteredFilterValue;
	
	public function __construct($unfilteredFilterValue = null){
		$this->availableFilters = array();
		$this->unfilteredFilterValue = $unfilteredFilterValue;
	}
	
	public function __get($property) {
        if (isset($this->$property)) {
            return $this->$property;
        }
		return null;
    }
    
	public function setFilterDefinition($filterTableAlias, $filterField, $filterName = null, $unfilteredFilterValue = null)
	{
		$filterName = empty($filterName) ? $filterField : $filterName; 
		$valueUnfiltered = isset($unfilteredFilterValue) ? $unfilteredFilterValue : $this->unfilteredFilterValue; 
		$this->availableFilters[$filterName] = new FilterItem($filterTableAlias, $filterField, $filterName, $filterValue = null, $filterOption = FilterItem::OPTION_EQUALS, $valueUnfiltered);
	}
	
	public function unsetFilterDefinition($filterName)
	{
		unset($this->availableFilters[$filterName]);
	}
	
	public function setFilterValue($filterName, $filterValue, $filterOption = self::OPTION_EQUALS)
	{
		if (isset($this->availableFilters[$filterName])) {
			$filterItem = $this->availableFilters[$filterName];
			$filterItem->setFilterValue($filterValue, $filterOption);
			return true;
		}
		else return false;
	}
	
	public function isAvailable($filterName)
	{
		return in_array($filterName, array_keys($this->availableFilters));
	} 
	
	public function getItem($filterName)
	{
		if ($this->isAvailable($filterName)) 
			return $this->availableFilters[$filterName];
		else 
			return false; 
	} 
	
	public function getFieldsString(array $requestedFields = null)
	{
		$fields = array();
		foreach ($this->availableFilters as $filterName => $filterItem) {
			if ( ! empty($requestedFields) && ! in_array($filterName, $requestedFields)) 
				continue;
			 
			$filterTableAlias = $filterItem->filterTableAlias;
			if ( ! empty($filterTableAlias)) $filterTableAlias = "$filterTableAlias.";
			$fieldExpr = $filterTableAlias . $filterItem->filterField;
			if ($filterItem->filterName != $filterItem->filterField) 
				$fieldExpr .= ' AS '.$filterItem->filterName;
			$fields[] = $fieldExpr;
		}
		return implode(", \n", $fields);
	}
	
	public function getQueryCondition() 
	{
		$filter_condition = '1';
		foreach ($this->availableFilters as $filterName => $filterItem) {
			$filterValue = $filterItem->filterValue;
			if (isset($filterValue)) {
				$filterField = $filterItem->filterField;
				$filterTableAlias = $filterItem->filterTableAlias;
				if ( ! empty($filterTableAlias)) $filterTableAlias = "$filterTableAlias.";
				if (is_a($filterValue, 'StatPeriod'))
					$filter_condition .= "\n AND ".$filterValue->getSqlExpression($filterTableAlias.$filterField);
				elseif (is_array($filterValue))
					$filter_condition .= "\n AND $filterTableAlias{$filterField} IN (".implode(',',$filterValue).")";
				elseif ($filterItem->filterOption !== FilterItem::OPTION_EQUALS) {
					if ($filterItem->filterOption !== FilterItem::OPTION_STARTS) $filterValue = "$filterValue%";
					if ($filterItem->filterOption !== FilterItem::OPTION_ENDS) $filterValue = "%$filterValue";
					if ($filterItem->filterOption !== FilterItem::OPTION_CONTAINS) $filterValue = "%$filterValue%";
					
					$filter_condition .= "\n AND $filterTableAlias{$filterField} LIKE '$filterValue'";
				}
				else
					$filter_condition .= "\n AND $filterTableAlias{$filterField} = '$filterValue'";
			}
		}
		return $filter_condition;
	}
	
	public function prepareOrderBy($orderByString) 
	{
		$orderBy = explode(',', $orderByString);
		foreach ($orderBy as $i=>$expr ) {
			$field = trim(preg_replace('/asc|desc/i','',$expr));
			if ( ! $this->isAvailable($field)) unset($orderBy[$i]);
		}
		return implode(', ',$orderBy);
	}
	
}

/* EOF */