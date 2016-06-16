<?php
namespace ngscz\NetteDebugger;

use DOMDocument;

class Debugger {
	
	private $html = array();
	
	public function run() {
		
		$action = (isset($_GET['action']))?$_GET['action']:false;
		
		switch ($action) {
			case 'show':
				$this->createExceptionPage($_GET['file']);
			break;
			
			case 'clearCache':
				$this->clearCache();
			break;

			case 'clearLog':
				$this->clearLog();
			break;
		
			case 'delete':
				$this->deleteExceptionFile($_GET['file']);
			break;
			
			default:
				$this->createHeader();
				$this->createTopButtons();
				$this->createLogTable();		
			break;
		}
				
		
		$this->createCssStyles();				
				
		$this->renderHTML();
	}
	
	private function renderHTML() {
		echo 
		sprintf('<div class="container">%s</div>', 
			implode('',$this->html)
		);
	}
	
	private function deleteExceptionFile($file) {
		$files = $this->getLogFiles();
		if (isset($files[$file])) {
			unlink($files[$file]);
			$this->redirectHome();
		} else {
			$this->html[] = 'Exception not found.';
		}				
	}
	
	private function clearCache() {
		$this->clearDir($this->getCacheDir());
		$this->redirectHome();
	}
	
	private function clearLog() {		
		foreach ($this->getLogFiles() as $file => $absPath) {
			if (in_array($file, array('.', '..'))) continue;	
			
			if (strstr($file, '.html') || strstr($file, '.log')) {				
				unlink($absPath);
			}
		}		
		$this->redirectHome();
	}
	
	private function createExceptionPage($file) {
		$files = $this->getLogFiles();
		if (isset($files[$file])) {
			if (strstr($file, '.html')) {
				$this->html[] = file_get_contents($files[$file]);
			} else {
				$this->html[] = '<pre style="height: 100%">' . file_get_contents($files[$file]) . '</pre>';
			}			
		} else {
			$this->html[] = 'Exception not found.';
		}
	}		

	private function redirectHome() {		
		header(sprintf('Location: %s', $_SERVER['SCRIPT_NAME']));
		exit();
	}
	
	private function createHeader() {
		$header = $this->renderContentTag('h1', 'Nette Debugger');
		$this->html[] = $this->renderContentTag('header', $header);
	}
	
	private function createTopButtons() {
		$this->html[] = $this->renderContentTag('a', 'Clear cache', array(
			'href'  => '?action=clearCache',
			'class' => 'btn btn-danger'
		));
		$this->html[] = $this->renderContentTag('a', 'Clear log', array(
			'href'  => '?action=clearLog',
			'class' => 'btn btn-danger'
		));		
		$this->html[] = $this->renderContentTag('div', '&nbsp;', array(			
			'class' => 'clearfix'
		));		
		
	}
	
	private function createLogTable() {
		$trs = array();
		foreach ($this->getLogFiles() as $file => $absPath) {
			$tds = array();
			$createdAtTime = false;
			
			//exception files
			if (strstr($absPath, '.html')) {
				$content = file_get_contents($absPath);
				$dom = new DOMDocument('1.0', 'UTF-8');
				@$dom->loadHTML($content);        						
				foreach ($dom->getElementsByTagName('title') as $elm) {
						$title = $elm->nodeValue;
						break;
				}			
				foreach ($dom->getElementsByTagName('ul') as $ulElm) {
					foreach ($ulElm->getElementsByTagName('li') as $liElm) {
						$liValue = $liElm->nodeValue;
						$liValue = trim(str_replace(array('Report generated at'), array(''), $liValue));
						$createdAtTime = strtotime($liValue);
						break;
					}												
					break;
				}			
				
			} else {
				$title = $file;
			}
			
			$tds[] = $this->renderContentTag('td', $title);
			
			$tds[] = $this->renderContentTag('td', 
				($createdAtTime)?date("j.n.Y H:i:s", $createdAtTime):' - '
			);
			
			//options
			$showLink = $this->renderContentTag('a', 'Show', array(
				'target' => '_blank',
				'href'	 => '?action=show&file=' . $file,
				'class'  => 'btn btn-success'
			));
			$deleteLink = $this->renderContentTag('a', 'Delete', array(				
				'href'	 => '?action=delete&file=' . $file,
				'class'  => 'btn btn-danger'
			));
			
			$tds[] = $this->renderContentTag('td', $showLink . $deleteLink);
			
			$trs[] = $this->renderContentTag('tr', implode('',$tds));
		}
		
		$table = $this->renderContentTag('table', implode('',$trs), array(
			'class' => 'table table-bordered table-striped'
		));
		
		$div = $this->renderContentTag('div', $table, array(
			'class'	=> 'table-responsive'
		));
		
		$this->html[] = $div;		
	}
	
	private function createCssStyles() {
		$styles = $this->renderContentTag('link', '', array(
			'rel'  => 'stylesheet', 
			'href' => '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css'
		));		
		
		$this->html[] = $styles;
	}
	
	private function renderContentTag($name, $content = '', $attributes = array()) {
		
		$htmlAttributes = array();
		foreach ($attributes as $aname => $avalue) {
			$htmlAttributes[] = sprintf('%s="%s"', $aname, $avalue);
		}
		
		return 
			sprintf('<%s %s>%s</%s>',
				$name,
				implode(' ', $htmlAttributes),
				$content,
				$name
			);
	}
	
	public function getLogFiles() {
		$logDir = $this->getLogDir();		
		$files = array();
		
		foreach (scandir($logDir) as $file) {
			if (in_array($file, array('.','..'))) continue;
			
			
			$absPath = $logDir . DIRECTORY_SEPARATOR . $file;
			
			$files[$file] = $absPath;						
		}		
		
		return $files;
	}
	
	public function getCacheDir() {
		return 
		dirname(__FILE__) . DIRECTORY_SEPARATOR .
		'..' . DIRECTORY_SEPARATOR .
		'..' . DIRECTORY_SEPARATOR . 
		'..' . DIRECTORY_SEPARATOR . 
		'..' . DIRECTORY_SEPARATOR . 
		'temp' . DIRECTORY_SEPARATOR .
		'cache'
		;
		
	}
	
	public function getLogDir() {
		return 
		dirname(__FILE__) . DIRECTORY_SEPARATOR .
		'..' . DIRECTORY_SEPARATOR .
		'..' . DIRECTORY_SEPARATOR . 
		'..' . DIRECTORY_SEPARATOR . 
		'..' . DIRECTORY_SEPARATOR . 
		'log';
	}
	
	public function clearDir($dir, $deleteDir = false) {
			if (is_dir($dir)) {
					$objects = scandir($dir);
					foreach ($objects as $object) {
							if ($object != "." && $object != "..") {
									if (filetype($dir . "/" . $object) == "dir") {
										$this->clearDir($dir . "/" . $object, true);
									} else {
										@unlink($dir . "/" . $object);
									}
							}
					}
					reset($objects);
					if ($deleteDir) {
						@rmdir($dir);
					}	
			}
	}
	
}