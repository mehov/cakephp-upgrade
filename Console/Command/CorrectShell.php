<?php 

App::uses('Folder', 'Utility');
App::uses('UpgradeShell', 'Console/Command');

/**
 * 2011-10-20 ms
 */
class CorrectShell extends UpgradeShell {


	public function all() {
		$all = array('tests', 'request', 'amp', 'vis', 'reference');
		foreach ($all as $name) {
			$this->out(__d('cake_console', 'Running %s', $name));
			$this->{$name};
		}
	}


	public function startup() {
		$this->params['ext'] = 'php|ctp|thtml|inc|tpl';
		$this->params['dry-run'] = false;
	}
	
	public function request() {
		$this->params['ext'] = 'php';
		$this->_getPaths();
		
		$patterns = array(		
			array(
				'RequestHandlerComponent::getClientIP() to CakeRequest::clientIP()',
				'/\bRequestHandlerComponent\:\:getClientIP\(\)/i',
				'CakeRequest::clientIP()'
			),
		);
			
		$this->_filesRegexpUpdate($patterns);				
	}
	
	
	public function tests() {
		$this->params['ext'] = 'php';
		$this->_getPaths();
		
		$patterns = array(
			array(
				'function startCase() to function startTest()',
				'/\bfunction startCase\(\)/i',
				'function startTest()'
			),
			array(
				'new (.*)Helper() to new (.*)Helper($View)',
				'/\bnew (.*)Helper\(\)/i',
				'new \1Helper(new View(null))'
			),
			/*
			array(
				'new (.*)Component() to new (.*)Component(new ComponentCollection())',
				'/\bnew (.*)Component\(\)/i',
				'new \1Component(new ComponentCollection())'
			),
			*/
		);
			
		$this->_filesRegexpUpdate($patterns);			
	}

	public function vis() {
		$this->params['ext'] = 'php';
		$this->_getPaths();
		
		$patterns = array(
			array(
				'var $ to public $',
				'/\bvar \$/i',
				'public $'
			),
			array(
				'private $ to protected $',
				'/\bprivate \$/i',
				'protected $'
			),
			array(
				'private function __',
				'/\bprivate function\b/i',
				'protected function'
			),
			array(
				'	function __',
				'/	function (.*)\(/',
				'	public function \1('
			),
			/*
			array(
				'private function __',
				'/\bprivate function __(?!construct|destruct|sleep|wakeup|get|set|call|toString|invoke|set_state|clone|callStatic|isset|unset])\w+\b/i',
				'protected function _\1'
			),
			array(
				'function __',
				'/\bfunction __/',
				'function _'
			),
			array(
				'function _construct(',
				'/\bfunction _construct\(/',
				'function __construct('
			),
			array(
				'function _destruct(',
				'/\bfunction _destruct\(/',
				'function __destruct('
			),
			*/
		);
		$skipFiles = array(
		);
		$skipFolders = array(
			'Vendor',
			'vendors',
			'Lib'.DS.'Vendor',
			'Lib'.DS.'vendors',
		);
		$this->_filesRegexpUpdate($patterns, $skipFiles, $skipFolders);			
	}


	protected function _getPaths() {
		if (!empty($this->args)) {
			$this->_paths = $this->args[0];
		} elseif (!empty($this->params['plugin'])) {
			$this->_paths = App::pluginPath($this->params['plugin']);
		} else {
			$this->_paths = APP;
		}
		
		if (empty($this->_paths)) {
			$this->error('Please pass working dir as param (cake reference /absDir)');
		} else {
			$this->_paths = (array)$this->_paths;
		}
	}

	public function amp() {
		$this->params['ext'] = 'php';
		$this->_getPaths();
		
		$patterns = array(
			array(
				'=& $this-> -> = $this->',
				'/=\s*\& \$this\-\>/',
				'= $this->'
			),
			array(
				'=& to =',
				'/=\s*\&\s/',
				'= '
			),
			
			array(
				'=& $ to = $',
				'/=\s*\&\s*\$[A-Z]/',
				'= $'
			),
		);
		$skipFiles = array(
		);
		$skipFolders = array(
		);
		$this->_filesRegexpUpdate($patterns, $skipFiles, $skipFolders);						
	}
	


	public function reference() {
		$this->params['ext'] = 'php';
		$this->_getPaths();
		
		$patterns = array(
			array(
				'(&$Model',
				'/\(\&\$Model\b/',
				'(Model $Model'
			),
			array(
				'(&$model',
				'/\(\&\$model\b/',
				'(Model $model'
			),
			array(
				'(&$Controller',
				'/\(\&\$Controller\b/',
				'(Controller $Controller'
			),
			array(
				'(&$controller',
				'/\(\&\$controller\b/',
				'(Controller $controller'
			),
			array(
				'(&$Component',
				'/\(\&\$Component\b/',
				'(Component $Component'
			),
			array(
				'(&$component',
				'/\(\&\$component\b/',
				'(Component $component'
			),
			array(
				'=& ClassRegistry::',
				'/=\s*&\s*ClassRegistry\:\:/',
				'= ClassRegistry::'
			),
			/*
			array(
				'=& $Controller -> = $Controller',
				'/=\& \$Controller/i',
				'= $Controller'
			),
			*/
			# careful: can destroy variable access inside methods
			array(
				'function *($Model',
				'/function (.*)\(\$Model\b/',
				'function \1(Model $Model'
			),
			array(
				'function *($model',
				'/function (.*)\(\$model\b/',
				'function \1(Model $model'
			),			
			array(
				'function *($Controller',
				'/function (.*)\(\$Controller\b/',
				'function \1(Controller $Controller'
			),
			array(
				'function *($controller',
				'/function (.*)\(\$controller\b/',
				'function \1(Controller $controller'
			),			
			array(
				'function *($Component',
				'/function (.*)\(\$Component\b/',
				'function \1(Component $Component'
			),
			array(
				'function *($component',
				'/function (.*)\(\$component\b/',
				'function \1(Component $component'
			),			
			array(
				'ComponentCollection $collection',
				'/\ComponentCollection \$collection/',
				'ComponentCollection $collection'
			),
			array(
				'ComponentCollection $Collection',
				'/\ComponentCollection \$Collection/',
				'ComponentCollection $Collection'
			),
			/*
			array(
				'parent::__construct($collection to parent::__construct($Collection',
				'/parent\:\:\_\_construct\(\$collection/',
				'parent::__construct($Collection'
			),
			
			array(
				'this->_Collection = $Collection;',
				'/this-\>\_Collection = \$collection/i',
				'this->_Collection = $Collection'
			),
			array(
				'$model -> $Model',
				'/\$model\b/',
				'$Model'
			),
			array(
				'$controller -> $Controller',
				'/\$controller\b/',
				'$Controller'
			),
			array(
				'$component -> $Component',
				'/\$component\b/',
				'$Component'
			),
			array(
				'$this->model -> $this->Model',
				'/\$this\-\>model\b/',
				'$this->Model'
			),
			array(
				'$this->controller -> $this->Controller',
				'/\$this\-\>controller\b/',
				'$this->Controller'
			),
			array(
				'$this->component -> $this->Component',
				'/\$this\-\>component\b/',
				'$this->Component'
			),
			array(
				'$this->renderAs($controller,',
				'/\$this->renderAs\(\$controller,/',
				'$this->renderAs($Controller,',
			),
			array(
				'$controller->',
				'/\$controller-\>/',
				'$Controller->',
			),
			*/
			array(
				'function fullTableName(Model $Model',
				'/function fullTableName\(Model \$Model/i',
				'function fullTableName($model',
			),
			/*
			array(
				'$controller = new Controller',
				'/\$controller = new /',
				'$Controller = new ',
			),
			array(
				'return $controller',
				'/return \$controller/',
				'return $Controller',
			),
			array(
				'if (is_object($controller))',
				'/if \(is_object\(\$controller\)\)/',
				'if (is_object($Controller))',
			),
			*/
			array(
				'function describe($Model)',
				'/function describe\(Model \$Model\)/',
				'function describe($Model)',
			),
			array(
				'function describe($model)',
				'/function describe\(Model \$model\)/',
				'function describe($model)',
			),
			/*
			array(
				'fetchAssociated($model',
				'/fetchAssociated\(\$model/',
				'fetchAssociated($Model',
			),
			array(
				'AssociationQuery($model',
				'/AssociationQuery\(\$model/',
				'AssociationQuery($Model',
			),
			array(
				'$model->',
				'/\$model-\>/',
				'$Model->',
			),
			
			array(
				'Model $model',
				'/Model \$model/',
				'Model $Model',
			),
			*/
			array(
				'Model $linkModel',
				'/Model \$linkModel/',
				'Model $LinkModel',
			),
			array(
				'$linkModel',
				'/\$linkModel\b/',
				'$LinkModel',
			),
			array(
				', &$linkModel',
				'/, \&\$linkModel/i',
				', Model $LinkModel',
			),
			array(
				'function index(Model $Model',
				'/function index\(Model \$Model/i',
				'function index($model',
			),
			/*
			array(
				'$model = ClassRegistry',
				'/\$model = ClassRegistry/',
				'$Model = ClassRegistry',
			),
			array(
				'$model = new',
				'/\$model = new /',
				'$Model = new ',
			),			
			array(
				'$table = $model->tablePrefix . $model->table',
				'/\$table = \$Model-\>tablePrefix . \$Model-\>table/',
				'$table = $model->tablePrefix . $model->table',
			),
			array(
				'$this->fields($model',
				'/\$this-\>fields\(\$model/',
				'$this->fields($Model',
			),
			array(
				' $this->fullTableName($model)',
				'/\$this-\>fullTableName\(\$model\)/',
				'$this->fullTableName($Model)',
			),
			array(
				'filterResults($resultSet, $model',
				'/filterResults\(\$resultSet, \$model/',
				'filterResults($resultSet, $Model',
			),
			array(
				'$db->queryAssociation($model',
				'/\$db-\>queryAssociation\(\$model/',
				'$db->queryAssociation($Model',
			),
			array(
				'		$model',
				'/		\$model\b/',
				'		$Model\b',
			),
			array(
				'$model',
				'/\),
				\$model/',
				'),
				$Model',
			),
			array(
				'x',
				'/get_class\(\$model\)/',
				'get_class($Model)',
			),
			array(
				'x',
				'/if \(is_object\(\$model\) && \$Model/',
				'if (is_object($Model) && $Model',
			),
			array(
				'x',
				'/is_object\(\$model\) \? \$Model/',
				'is_object($Model) ? $Model',
			),
			array(
				'x',
				'/array\(\$operator =\> array\(\$key =\> \$value\)\), true, \$model/',
				'array($operator => array($key => $value)), true, $Model',
			),
			array(
				'x',
				'/\$model = \$this-\>getObject/',
				'$Model = $this->getObject',
			),
			array(
				'x',
				'/if \(is_object\(\$model\) && \(is_a\(\$model, \$class/',
				'if (is_object($Model) && (is_a($Model, $class',
			),
			array(
				'x',
				'/\$duplicate = \$model;/',
				'$Duplicate = $Model;',
			),
			array(
				'x',
				'/return \$duplicate/',
				'return $duplicate',
			),
			array(
				'x',
				'/\$this-\>_addToWhitelist\(\$model/',
				'$this->_addToWhitelist($Model',
			),
			array(
				'x',
				'/\$this-\>fullTableName\(\$model/',
				'$this->fullTableName($Model',
			),
			array(
				'x',
				'/\$duplicate = false;/',
				'$Duplicate = false;',
			),
			array(
				'method_exists($model',
				'/method_exists\(\$model/',
				'method_exists($Model'
			),
			array(
				'$this->node($model',
				'/\$this-\>node\(\$model/',
				'$this->node($Model'
			),
			array(
				'afterSave($model',
				'/afterSave\(\$model/',
				'afterSave($Model'
			),
			array(
				'x',
				'/}
			unset\(\$model\);/',
				'}
			unset($Model);',
			),
			*/
			array(
				'_parseKey(Model $model',
				'/_parseKey\(Model \$model/',
				'_parseKey($model'
			),
			array(
				'array(Controller $Controller));',
				'/array\(Controller \$Controller\b/',
				'array(&$Controller'
			),
			array(
				'array(Controller $controller));',
				'/array\(Controller \$controller\b/',
				'array(&$controller'
			),
		);
		$skipFiles = array(
			'ComponentCollection.php',
			'BehaviorCollection.php',
			'FixtureTask.php',
			'FormHelper.php',
			'PaginatorHelper.php',
			'ControllerTestCase.php',
			'Router.php',
			'JsHelperTest.php',
			'JqueryEngineHelperTest.php'
		/*
			
			'Mysql.php',
			'BakeShell.php',
			'ConsoleShell.php',
			
		
			'ContainableBehaviorTest.php'
		*/
		);
		$skipFolders = array(
			'TODO__'
		);
		$this->_filesRegexpUpdate($patterns, $skipFiles, $skipFolders);
		
		
		$file = $this->_paths[0].DS.'View'.DS.'View.php';
		if (file_exists($file)) {
			$content = file_get_contents($file);
			$content = str_replace('__construct(Controller $controller)', '__construct(Controller $controller = null)', $content);
			file_put_contents($file, $content);
			/*
			array(
				'x',
				'/construct\(Controller \$Controller\)/i',
				'construct(Controller $Controller = null)',
			);
			$this->_updateFile($file, $patterns);
			*/
		}	else {
			die('FILE NOT EXISTS');
		}
	}

	/**
	 * Update legacy stuff for 2.0.
	 *
	 * - Replaces App::import() with App::uses() - mainly Utility classes.
	 *
	 * @return void
	 */
	public function objects() {
		$this->_getPaths();
		
		//die(print_r($this->_paths, true));
		$patterns = array(
			array(
				'$component -> $Component',
				'/\$component\b/',
				'$Component'
			),
			array(
				'$controller -> $Controller',
				'/\$controller\b/',
				'$Controller'
			),
			array(
				'$collection -> $Collection',
				'/\$collection\b/',
				'$Collection'
			),
		);
		$skipFiles = array(
			'ControllerTask.php', 'BakeShell.php', 'ControllerTask',
			'ControllerTaskTest.php', 'ViewTaskTest.php', 'AppTest.php', 
			'ControllerTestCase.php', 'missing_action.ctp', 'private_action.ctp',
			'controller.ctp', 'Router.php', 
			'ComponentCollection.php', # !!!
		);

		$this->_filesRegexpUpdate($patterns, $skipFiles);		
		
		# manually adjust dispatcher
		$patterns = array(
			array(
				'= $controller =',
				'/= \$Controller =/',
				'= $controller ='
			),
			array(
				'if ($pluginPath . $controller)',
				'/if \(\$pluginPath \. \$Controller\)/',
				'if ($pluginPath . $controller)'
			),
			array(
				'$controller = Inflector',
				'/\$Controller = Inflector/',
				'$controller = Inflector'
			),
			array(
				'$class = $controller .',
				'/\$class = \$Controller \./',
				'$class = $controller .'
			),
		);		
		$this->_paths[0] = $this->_paths[0].DS.'Routing';		
		//die(print_r($this->_paths, true));
		$skipFiles = array('Router.php');
		$this->_filesRegexpUpdate($patterns, $skipFiles);							
	}


	/**
	 * Updates files based on regular expressions.
	 *
	 * @param array $patterns Array of search and replacement patterns.
	 * @return void
	 */
	protected function _filesRegexpUpdate($patterns, $skipFiles = array(), $skipFolders = array()) {
		$this->_findFiles($this->params['ext'], $skipFolders);
		foreach ($this->_files as $file) {
			if (in_array(pathinfo($file, PATHINFO_BASENAME), $skipFiles)) {
				continue;
			}
			$this->out(__d('cake_console', 'Updating %s...', $file), 1, Shell::VERBOSE);
			$this->_updateFile($file, $patterns);
		}
	}


/**
 * Searches the paths and finds files based on extension.
 *
 * @param string $extensions
 * @return void
 */
	protected function _findFiles($extensions = '', $skipFolders = array()) {
		foreach ($this->_paths as $path) {
			if (substr($path, -1) != DS) {
				$path .= DS;
			}
			if (!is_dir($path)) {
				continue;
			}
			if (!empty($skipFolders) && in_array(basename($path), $skipFolders)) {
				continue;
			}
			$this->_files = array();
			$Iterator = new RegexIterator(
				new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)),
				'/^.+\.(' . $extensions . ')$/i',
				RegexIterator::MATCH
			);
			foreach ($Iterator as $file) {
				# Iterator processes plugins even if not asked to
				$excludes = array();
				if (empty($this->params['plugin'])) {
					$excludes = array('Plugin', 'plugins');
				}
				$excludes = am($excludes, $skipFolders);
				//echo returns($excludes); die();
				
				$isIllegalPath = false;
				foreach ($excludes as $exclude) {
					if (strpos($file->getPathname(), $path . $exclude . DS) === 0) {
						$isIllegalPath = true;
						break;
					}
				}
				if ($isIllegalPath) {
					continue;
				}
				//$this->out( $file->getPathname() ); continue;
				
				if ($file->isFile()) {
					$this->_files[] = $file->getPathname();
				}
			}
		}
	}


	public function getOptionParser() {
		$subcommandParser = array(
			'options' => array(
				'plugin' => array(
					'short' => 'p',
					'help' => __d('cake_console', 'The plugin to update. Only the specified plugin will be updated.'),
					'default' => '',
				),
				'dry-run'=> array(
					'short' => 'd',
					'help' => __d('cake_console', 'Dry run the update, no files will actually be modified.'),
					'boolean' => true
				),
				'log'=> array(
					'short' => 'l',
					'help' => __d('cake_console', 'Log all ouput to file log.txt in TMP dir'),
					'boolean' => true
				)
			)
		);
		
		return parent::getOptionParser()
			->description(__d('cake_console', "A shell to help automate upgrading from CakePHP 1.3 to 2.0. \n" .
				"Be sure to have a backup of your application before running these commands."))
			->addSubcommand('all', array(
				'help' => __d('cake_console', 'Run all correctional commands'),
				'parser' => $subcommandParser
			))
			->addSubcommand('objects', array(
				'help' => __d('cake_console', 'Update objects'),
				'parser' => $subcommandParser
			))
			->addSubcommand('reference', array(
				'help' => __d('cake_console', 'Update reference'),
				'parser' => $subcommandParser
			))
			->addSubcommand('amp', array(
				'help' => __d('cake_console', '=& fix'),
				'parser' => $subcommandParser
			))
			->addSubcommand('vis', array(
				'help' => __d('cake_console', 'visibility (public, protected)'),
				'parser' => $subcommandParser
			));
	}


}
