<?PHP

class Page
{
	private $template;
	
	function __construct(){ 
		global $config;

		$this->template = new Smarty();

		if ($config->environment == 'production'){
			$this->template->compile_check=false;
		}
	}
	
	public function show(){
		global $q, $where,  $texts, $col, $site, $filter, $filterLabel,$filter_chain, $from, $count, $index, $result, $lang, $config, $printMode, $detail, $colectionData, $sort, $fmt;

		if (isset($q) && $q != ''){
			$getParams .= "&q=" . urlencode(utf8_decode($q));
		}
		if (isset($filter) && $filter != ''){
			$getParams .= "&filter=" . str_replace("\\\"","&quot;",$filter);
		}
		if (isset($where) && $where != 'ALL'){
			$getParams .= "&where=" . $where;
		}
		if (isset($index) && $index != ''){
			$getParams .= "&index=" . $index;
		}
		if (isset($from) && $from != ''){
			$getParams .= "&from=" . $from;
		}
		if (isset($filter_chain)){
			foreach($filter_chain  as $filterValue ){
				$getParams .= "&filter_chain[]=" . str_replace("\\\"","&quot;",$filterValue);
			}
		}
        if (isset($sort) && $sort != ''){
            $getParams .= "&sort=" . $sort;
        }

		
		$q_escaped = str_replace("\"","&quot;",$q);
		$textsCol = parse_ini_file("./languages/" . $lang . "/texts-" . $col . ".ini", false);

		$this->template->assign('lang',$lang);
		$this->template->assign('texts',$texts + $textsCol);
		$this->template->assign('printMode',$printMode);
		$this->template->assign('detail',$detail);
		$this->template->assign('config',$config);
		$this->template->assign('q_escaped',$q_escaped);
		$this->template->assign('col',$col);
		$this->template->assign('site',$site);
		$this->template->assign('filter',$filter);
		$this->template->assign('filterLabel',$filterLabel);
		$this->template->assign('filter_chain',$filter_chain);
		$this->template->assign('index',$index);
		$this->template->assign('from',$from);
        $this->template->assign('fmt',$fmt);
		$this->template->assign('numFound',$result->diaServerResponse[0]->response->numFound);
		$this->template->assign('colectionData',$colectionData);
		$this->template->assign('getParams',$getParams);

		$total = $result->diaServerResponse[0]->response->numFound;
		$pagination = $this->pagination($from, $count, $total);

		$this->template->assign('pagination',$pagination);
		$this->template->assign('result',$result->diaServerResponse[0]);
		$this->template->assign('links',$result->diaServerResponse[1]);

		// display TOP (banner, searchbar)
		$this->template->display('top.tpl');

		// display RESULT (refine, docs)
		if ($detail == '1'){
			$this->template->display('result-detail.tpl');
		}else{
			$this->template->display('result.tpl');
		}
		// display BOTTOM
		$this->template->display('bottom.tpl');

	}

	public function RSS(){
		global $col, $texts, $result, $lang;
		
		$textsCol = parse_ini_file("./languages/" . $lang . "/texts-" . $col . ".ini", false);
		$url = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT'].$_SERVER['SCRIPT_NAME'];

		$this->template->assign('url',$url);
		$this->template->assign('lang',$lang);
		$this->template->assign('texts',$texts + $textsCol);
		$this->template->assign('result',$result->diaServerResponse[0]);

		$this->template->display('export-rss.tpl');
	}


	public function email(){
		global $col, $texts, $result, $lang, $from, $count;
		
		$textsCol = parse_ini_file("./languages/" . $lang . "/texts-" . $col . ".ini", false);
		$url = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT'].$_SERVER['SCRIPT_NAME'];
		$url = str_replace("mail.php","",$url);

        $total = $result->diaServerResponse[0]->response->numFound;
        $pagination = $this->pagination($from, $count, $total);

        $this->template->assign('pagination',$pagination);
		$this->template->assign('url',$url);
		$this->template->assign('texts',$texts + $textsCol);
		$this->template->assign('result',$result->diaServerResponse[0]);

		return $this->template->fetch('email-body.tpl');
	}

	private function pagination($from, $count, $total){

		$from = ($from != 0 ? $from : 1);
		
		if ( ($from + $count) <= $total ){
			$to = ($from + $count)-1;
		}else{
			$to = $total;	
		}

		$pagination['from'] = $from;
		$pagination['to'] = $to;
		$pagination['count'] = intval($count);
		$pagination['total'] = $total;
		$pagination['page'] = ceil($from / $count);
		$pagination['last'] = ceil($total / $count);	
		$pagination['npages'] = ceil($total / $count);
		$rangeSet = ( ($from/$count) / 10);
		
		$range = ( $rangeSet >= 1 ? intval($rangeSet) : '');

		for ($i = 1; $i <= 9; $i++) {
			if ( intval($range.$i) <= $pagination['npages']){
				$pagination['pages'][] = $range . $i;
			}
		}
		if (intval($range.$i) < $pagination['npages']){
			// ultima página do conjunto (multiplo de 10)
			$pagination['pages'][] = 10 * ($range+1);		
		}
		return $pagination;

	}

}

?>