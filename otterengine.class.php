<?php
/*******************************************************
 * Otter Template/Expedition Engine
 * @Author Kelly Farris
 * 
 * @Version 1.0
 * 
 * 
 * 
 * @Copyright 2014 Kelly Farris
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * See the LICENSE file for more details.
*/
 
class otter
{
	/**
	 * array that stores the 'seed', and any applicable data
	 * @var unknown
	 */
	private $tag = array();
	
	/**
	 * The following are currently unused after a change to the handling of scripts
	 * @var unknown
	 */
	private $script_path = null;
	private $css_path = null;
	private $css = array();
	
	/**
	 * Array where the paths for the scripts/css are stored
	 * @var unknown
	 */
	private $scripts = array();
	
	
	/**
	 * Array that stores defined meta tags
	 * @var unknown
	 */
	private $meta = array();
	
	/**
	 * array that initially stored 'variables'.  
	 * Currently it's been deemed redundant.
	 * @var unknown
	 */
	private $vars = array();
	
	/**
	 * Array containing defined conditionals
	 * @var unknown
	 */
	private $conditional = array();
	
	/**
	 * initially contained the rendered page.
	 * This page is now stored in a $_SESSION variable
	 * @var unknown
	 */
	private $cached_render = null;
	
	/**
	 * If set to true, cached page will be cleared
	 * @var unknown
	 */
	private $clear_cache = false;
	
	/**
	 * If set to true, cached page will be displayed.
	 * Don't use if you have dynamic content.
	 * @var unknown
	 */
	private $use_cache = false;
	
	/**
	 * Array containing defined incrementals.
	 * @var unknown
	 */
	private $inc = array();
	
	/**
	 * Array containting defined forms.
	 * @var unknown
	 */
	private $form = array();
	
	
	
	function __construct()
	{
		
	}
	
	
	/**
	 * Deletes all defined html elements associated with the specified seed name.
	 * @param string $seed
	 */
	function clearSeed($seed)
	{
		unset($this->tag[$seed]);
	}
	
	/**
	 * Adds the seed and HTML to the array.
	 * @param string $tag
	 * @param string $seed
	 * @param string $build_string
	 */
	private function createTag($tag,$seed,$build_string)
	{
		
			$this->tag[$seed][] = $build_string;
	}
	
	
	/**
	 * Defines a Generic or Basic element.  This should be a fully formed HTML tag, including any required 
	 * attributes.
	 * @param unknown $seed
	 * @param unknown $html
	 */
	function addBasicElement($seed,$html)
	{
		$this->createTag('tag', $seed, $html);
	}
	
	/**
	 * Defines a generic label.  Can use attributes to associate it with another element.
	 * @param string $seed
	 * @param string $text
	 * @param string $attributes
	 */
	function addLabel( $seed,$text,$attributes = array())
	{
		if(!empty($attributes))
		{
			$options = $this->innerTags($attributes);
		}else{
			$options = "";
		}
		
			//$this->tag[$seed] = "<label$options>$text</label>";
			$build_string = "<label$options>$text</label>";
			$this->createTag('tag',$seed,$build_string);
	}
	
	/**
	 * Defines a TABLE element
	 * CURRENTLY UNFINISHED - Will result in a lot of nothing.
	 * @param unknown $seed
	 * @param array $header
	 * @param array $content
	 * @param array $header_attributes
	 * @param array $body_attributes
	 */
	function addTable($seed,array $header,array $content,array $header_attributes = array(),array $body_attributes=array())
	{
		$header_options = $this->innerTags($header_attributes);
		$body_options = $this->innerTags($attributes);
		
		$header_build ="<tr>";
		foreach($header as $value)
		{
			$header_build .= "<th $header_options>$value</th>";
		}
		$header_build .="</tr>";
		
		$content_build = "<tr>";
		foreach($content as $row_key=>$row)
		{
			$content_build = "<tr>";
			foreach($row as $value)
			{
				$content_build .= "<td $body_options>$value</td>";
			}
			$content_build .="</tr>";
		}
		
		$body_build .="<tbody>".$content_build . "</tbody>";
		$build_string = "<table><thead>";
		//todo finish this part.  
	}
	
	/**
	 * Defines an Anchor or LINK element.  Use the attributes argument to define additional HTML5 attributes.
	 * @param string $seed
	 * @param string $href 
	 * @param string $innerText
	 * @param array $attributes [optional]
	 */
	function addLink( $seed, $href, $innerText, $attributes=array())
	{
		$options = $this->innerTags($attributes);
		
		$build_string = "<a href='$href' $options>$innerText</a>";
		$this->createTag('tag',$seed,$build_string);
		//$this->tag[$seed] = $build_string;
	}
	
	
	/**
	 * Defines a label element and returns it to be included in the defined tag.
	 * @param string $id
	 * @param string $label
	 * @return string
	 */
	private function createLabel($id,$label)
	{
		if(!empty($id) && !empty($label))
		{
			$s_label = "<label for='$id'>$label</label>";
			return $s_label;
		}
		//return an empty string if $id or $label are empty
		return "";
	}
	
	
	/**
	 * Defines a SELECT element and its OPTIONS
	 * @param string $seed
	 * @param array $options Multidimensional Array with VALUE=>INNERTEXT
	 * @param array $attributes [optional] Element attributes
	 * @example array('class'=>'my-class','id'=>'my-select')
	 * @param string $label [option] Requires the attribute 'id' to be set.  Will create a LABEL element for the SELECT
	 * @param string $selected_option [optional]  If this matches either the VALUE or INNERTEXT, it will set that
	 * option as 'selected'
	 */
	function addSelect($seed, array $options, $attributes = array(),$label=null,$selected_option = null)
	{
		$s_label = "";
		//adds a label to the element (if defined)
		if(isset($attributes['id']))
		{
			$s_label = $this->createLabel($attributes['id'],$label);
		}
		
		$inner_tags = $this->innerTags($attributes);
		$build_string = "<select " . $inner_tags . ">";
		
		$option = "";
		foreach($options as $value => $innerText)
		{
			
				if(!empty($selected_option) && ($selected_option == $value || $selected_option ==$innerText))
				{
					$option .= "<option value='$value' selected>$innerText</option>";
				
				}else{
					$option .= "<option value='$value'>$innerText</option>";
				}
			
		}
		
		//$this->tag[$seed] = $s_label . $build_string . $option . "</select>";
		$this->createTag('tag',$seed, $s_label . $build_string . $option . "</select>");
	}
	
	
	
	
	/**
	 * Defines a form and all of it's elements.
	 * elements should be defined separately.
	 * 
	 * @param string $seed
	 * @param array $form_options
	 */
	function addForm($seed,array $form_elements,array $form_attributes)
	{
		$innerText = $this->innerTags($form_attributes);
		foreach($form_elements as $tag)
		{
			$this->form[$seed]['element'][] = $tag;
		}
		
		$build_string ="<form " . $innerText . ">";
		$this->form[$seed]['form'] = $build_string;
	}
	
	function addImage($seed, $src, $attributes=array())
	{
		$options = $this->innerTags($attributes);
		
		$build_string = "<img src='$src'$options>";
		//$this->tag[$seed] = $build_string;
		$this->createTag('tag',$seed,$build_string);
	}
	/**
	 * Defines an input.
	 * @param string $seed
	 * @param string $type text,password,email,phone, etc.  Any HTML5 types.
	 * @param array $attributes [optional] An multi-dimensional array containting attributes such as id, class, name, data, etc.
	 */
	function addInput($seed,$type,$attributes=array(),$required = false)
	{
		$options = $this->innerTags($attributes);
		$req = ($required == true ? 'required' : null);
		$build_string = "<input type='$type' $options $req >";
		//$this->tag[$seed] = $build_string;
		$this->createTag('tag',$seed,$build_string);
	}
	
	
	/**
	 * Defines a text area.
	 * @param string $seed
	 * @param string $text
	 * @param array $attributes [optional]
	 */
	function addTextArea($seed,$text,$attributes=array())
	{
		$options = $this->innerTags($attributes);
		$build_string = "<textarea $options>$text</textarea>";
		//$this->tag[$seed] = $build_string;
		$this->createTag('tag',$seed,$build_string);
		
	}
	
	/**
	 * Defines a canvas.
	 * @param string $seed
	 * @param array $attributes [optional]
	 */
	function addCanvas($seed,$attributes=array())
	{
		$options = $this->innerTags($attributes);
		$build_string = "<canvas $options ></canvas>";
		//$this->tag[$seed] = $build_string;
		$this->createTag('tag',$seed,$build_string);
	}
	
	
	/**
	 * Defines a definition list.  A definition list will create a list of pref-defined options for an <input> element.
	 * Use the 'list=id' attribute within the <input> element to associate the list.
	 * @param string $seed
	 * @param string $id
	 * @param array $data  one-dimensional array of elements.
	 * @param array $attributes [optional]
	 */
	function addDatalist($seed,$id,array $data,$attributes = array())
	{
		$data_list = null;
		foreach($data as $value)
		{
			$data_list .="<option value='$value'>";
		}
		$build_string= "<datalist id='$id'>" .$data_list ."</datalist>";
		//$this->tag[$seed] = $build_string;
		$this->createTag('tag',$seed,$build_string);
	}
	
	/**
	 * Defines an EMBED tag.
	 * @param string $seed
	 * @param string $src
	 * @param array $attributes [optional]
	 */
	function addEmbed($seed,$src,$attributes=array())
	{
		$options = $this->innerTags($attributes);
		$build_string = "<embed src='$src' $attributes >";
		//$this->tag[$seed] = $build_string;
		$this->createTag('tag',$seed,$build_string);
	}
	
	/**
	 * Defines a FIGURE tag
	 * @param string $seed
	 * @param string $src
	 * @param string $caption [optional]
	 * @param array $attributes [optional]
	 */
	function addFigure($seed,$src,$caption=null,$attributes=array())
	{
		$options = $this->innerTags($attributes);
		$build_string="<figure><img src='$src' $attributes ><figcaption>$caption</figcaption></figure>";
		//$this->tag[$seed] = $build_string;
		$this->createTag('tag',$seed,$build_string);
	}
	
	/**
	 * Defines CSS/Stylesheet.
	 * @param string $seed
	 * @param string $path
	 */
	function addCSS($seed,$path)
	{
		$build_string = "<link rel='stylesheet' type='text/css' href='$path'>";
		//$this->tag[$seed] = $build_string;
		$this->createTag('tag',$seed,$build_string);
	}
	
	/**
	 * Defines multiple CSS or JavaScripts. 
	 * All scripts are loaded in the order they in the array.
	 * @param string $seed A unique identifier or Tag that will be called in the template.
	 * @param array $scripts One-dimensional array that contains paths to all required scripts.
	 * @example array("//google.com/api/jquery/jquery.1.10.min.js","css/styles.css","scripts/myscript.js")
	 * OtterEngine will determine if it is a CSS or JavaScript source automatically.
	 * Scripts are inserted in the order they are in the array.
	 */
	function addBulkScripts($seed,array $scripts)
	{
		$build_scripts=null;
		foreach($scripts as $filename)
		{
			
			if(strpos($filename,".js"))
			{
				$build_scripts .=" <script src='$filename'></script> ";
			}
			
			if(strpos($filename,".css"))
			{
				
				$build_scripts .=" <link rel='stylesheet' type='text/css' href='$filename'> ";
			}
		}
		$this->scripts[$seed] = $build_scripts;
		
	}
	
	/**
	 * Defines meta tags.  
	 * @example array("name"=>"author","description"=>"J.K. Rowling");
	 * @param array $content
	 */
	function addMeta(array $attributes)
	{
		$meta= null;
	
		foreach($attributes as $name=>$content)
		{
			$meta .= $name . "='$content' ";
		}
		
		$build_string = "<meta $meta >";
		$this->meta[] = $build_string;
	}
	
	/**
	 * Defines a METER tag.
	 * @param string $seed
	 * @param string $value
	 * @param string $min
	 * @param string $max
	 * @param string $text
	 * @param array $attributes
	 */
	function addMeter($seed,$value,$min,$max,$text,$attributes=array())
	{
		$options = $this->innerTags($attributes);
		$build_string = "<meter value='$value' min='$min' max='$max' $options>$text</meter>";
		//$this->tag[$seed] = $build_string;
		$this->createTag('tag',$seed,$build_string);
	}
	
	
	/**
	 * Defines a NAV element, including links
	 * Links should be defined in an array("< a href='http://mylink.com'>My LInk</a>")
	 * @param string $seed
	 * @param array $links Can be defined OtterEngine links, such as {{link1}}
	 * @param string $separator [optional] Defines a character to separate the nav link elements.
	 * @param array $attributes [optional] attributes for the NAV tag
	 */
	function addNav($seed,array $links,$separator="",$attributes = array())
	{
		$options = $this->innerTags($attributes);
		$s_links = null;
		foreach($links as $href=>$text)
		{
			$s_links .= $text ." ". $separator;
		}
		
		$s_links = trim($s_links,$separator);
		$build_string = "<nav $options> ". $s_links . "</nav>";
		//$this->tag[$seed] = $build_string;
		$this->createTag('tag',$seed,$build_string);
	}
	
	/**
	 * Defines a NOSCRIPT element.
	 * @param string $seed
	 * @param string $text
	 * @param array $attributes [optional]
	 */
	function addNoScript($seed,$text,$attributes=array())
	{
		$options = $this->innerTags($attributes);
		$build_string = "<noscript $options>$text</noscript>";
		//$this->tag[$seed] = $build_string;
		$this->createTag('tag',$seed,$build_string);
	}
	
	
	/**
	 * Defines a TITLE tag.
	 * @param unknown $seed
	 * @param unknown $title
	 */
	function addTitle($seed,$title)
	{
		$build_string = "<title>$title</title>";
		//$this->tag[$seed] = $build_string;
		$this->createTag('tag',$seed,$build_string);
	}
	
	/**
	 * Defines an ORDERED LIST
	 * @param string $seed
	 * @param array $list_items a one-dimensional array of the items.  Can be defined OtterEngine tags.
	 * @param array $attributes [optional]
	 */
	function addOrderedList($seed,array $list_items,$attributes=array())
	{
		$options = $this->innerTags($attributes);
		$items = null;
		foreach($list_items as $item)
		{
			$items .= "<li>$item</li>";
		}
		$build_string = "<ol $options> $items </ol>";
		//$this->tag[$seed] = $build_string;
		$this->createTag('tag',$seed,$build_string);
	}
	
	/**
	 * Defines Unordered List
	 * 
	 * @param unknown $seed
	 * @param array $list_items one-dimensional array of items.  Can be defined OtterEngine tags.
	 * @param array $attributes for the UL tag
	 */
	function addUnorderedList($seed,array $list_items,$attributes=array())
	{
		$options = $this->innerTags($attributes);
		$items = null;
		foreach($list_items as $item)
		{
			$items .= "<li>$item</li>";
		}
		$build_string = "<ul $options> $items </ul>";
		//$this->tag[$seed] = $build_string;
		$this->createTag('tag',$seed,$build_string);
	}
	
	
	/**
	 * Defines a PROGRESS bar element
	 * @param string $seed
	 * @param string $value
	 * @param string $max
	 * @param array $attributes [optional]
	 * @tutorial leave $value and $max blank to have a sweeping progress bar <progress></progress>
	 * @tutorial The PROGRESS tag is not supported in Internet Explorer 9 and earlier versions.
	 */
	function addProgress($seed,$value,$max,$attributes = array())
	{
		if(empty($value))
		{
			$val = "";
		}else{
			$val ="value='$value'";
		}
		
		if(empty($max)){
			$max_s = "";
		}else{
			$max_s = " max = '$max' ";
		}
		
		$options = $this->innerTags($attributes);
		$build_string = "<progress $val $max_s $options></progress>";
		//$this->tag[$seed]=$build_string;
		$this->createTag('tag',$seed,$build_string);
		
	}
	/**
	 * Compares $check == $against
	 * if it returns true, $result1 is displayed
	 * else
	 * result2 is displayed
	 * @tutorial Conditionals are processed FIRST.  This way you can define a otter tag as a result.
	 *	<p>$image1 = "{{my-image1}}";</p>
	 *  <br>$image2 = "{{my-image2}}";</br>
	 * 	<br>$otter->addImage("my-image1","loading1.gif");</br>
	 *	<br>$otter->addImage("my-image2","loading2.gif");</br>
	 * 	<br>$otter->addConditional("conditional1",$var1,$var2,$image1,$image2);</br>
	 * 	<br>if $var1 == $var2, loading1.gif will be displayed.</br>
	 * 
	 * @param string $seed Unique id or tag name.
	 * @param mixed $check variable to check 
	 * @param mixed $against variable to check against
	 * @param string $result1 the result that will be displayed on the page
	 * @param string $result2  the alternative result that will be displayed if conditional returns FALSE
	 */
	function addConditional($seed,$check,$against,$result1,$result2)
	{
		if($check == $against)
		{
			$this->conditional[$seed]=$result1;
		}else{
			$this->conditional[$seed]=$result2;
		}
	}
	
	/**
	 * Defines an AUDIO element.
	 * @param string $seed
	 * @param array $sources src and type
	 * @param string $support_message 
	 * @param array $attributes
	 * @example $otter->addAudio('audio', array("/song.mpg"=>"audio/mpg"), 'Your browser doesn\'t support audio');
	 */
	function addAudio($seed,array $sources, $support_message,$attributes=array())
	{
		$options = $this->innerTags($attributes);
		$s_sources = null;
		foreach($sources as $src=>$type)
		{
			$s_sources .= "<source src='$src' type='$type'>";
		}
		$build_string = "<audio controls $options> $s_sources </audio>";
		//$this->tag[$seed] = $build_string;
		$this->createTag('tag',$seed,$build_string);
	}
	
	
	/**
	 * Loads an external HTML or template file and renders it to the designated seed.
	 * @param string $seed
	 * @param string $filename
	 */
	function includeHTML($seed,$filename)
	{
		$html = file_get_contents($filename);
		$this->includes[$seed] = $html;
		$this->createTag('tag',$seed,$html);
	}
	
	/**
	 * Defines a seed or tag that will increment by the defined value with each use on the template
	 * 
	 * the prefix will be appended to the incremental number
	 * @example: $prefix = "button".  will appear as button1
	 * 
	 * @param string $seed Unique id or tag for the element
	 * @param string $prefix A prefix that will be appended to the incremented number. 
	 * @param int $start = 1 The number at which to start.  Default is 1
	 * @param int $inc = 1 The value in which to increment by. Default is 1.
	 */
	function addIncremental($seed,$prefix,$start = 1,$inc = 1)
	{
		$this->inc[$seed]['prefix']=$prefix;
		$this->inc[$seed]['start']=$start;
		$this->inc[$seed]['inc']=$inc;
	}
	
	
	/**
	 * Returns the rendered includes
	 * @param unknown $template
	 * @return mixed
	 */
	private function loadIncludes($template)
	{
		$build_string = null;
		if(!empty($this->includes))
		{
			foreach($this->includes as $seed=>$content)
			{
				$template = preg_replace("/{{".$seed."}}/", $content, $template);
			}
		}
	
	
		return $template;
	}
	
	
	
	/**
	 * Loads all scripts defined with addBulkScripts
	 * @param unknown $template
	 * @return mixed
	 */
	private function loadScripts($template)
	{
		$build_string = null;
		if(!empty($this->scripts))
		{
			foreach($this->scripts as $seed=>$content)
			{
				$template = preg_replace("/{{".$seed."}}/", $content, $template);
			}
		}
		
		
		return $template;
	}
	
	/**
	 * Replaces meta tag with META
	 * @param unknown $template
	 * @return mixed
	 */
	private function loadMeta($template)
	{
		$m_string = null;
		foreach($this->meta as $meta)
		{
			$m_string .= $meta;
		}
		$template = preg_replace("/{{meta}}/", $m_string, $template);
		return $template;
	}
	
	/**
	 * MAIN RENDERING METHOD
	 * Renders and returns the specified template with all defined tags.
	 * @param string $path Path To Template
	 * @return mixed Rendered Template
	 */
	function renderTemplate($path)
	{
		
		
			$cache = $this->getCachedPage($path);
		if( $cache && $this->use_cache==true)
		{
			 
			return $cache . "<!--otter - rendered from cache-->";
		}
		
		$template = file_get_contents($path);
		
		
		if(!$template)
		{
			echo "Unable to load template at using path: $path";
			return false;
		}
		
		/*load these before performing the final render, as seeds may be planted before
		 * from here.
		 */
		$template = $this->loadIncludes($template);
		$template = $this->scanForBlocks($template);
		$template = $this->runConditionals($template);
		$template = $this->loadScripts($template);
		$template = $this->loadMeta($template);
		$template = $this->renderForms($template);
		//$template = $this->echoVariables($template);
		
		
		
		//run this twice to replace any tags added AFTER first scan (ie. embedded tags)
		/*
		foreach($this->tag as $seed=>$content)
			
		{
			$template = preg_replace("/{{".$seed."}}/", $content, $template);
		}
		
		foreach($this->tag as $seed=>$content)
				
		{
			$template = preg_replace("/{{".$seed."}}/", $content, $template);
		}*/
		
		$template = $this->fetchTags($template);
		$template = $this->fetchTags($template);
		
		$template = $this->renderIncrements($template); //do this here so incrementals are rendered correctly
		$this->cachePage($path,$template);
		
		return $template . "<!--otter rendered live-->";
	}
	
	/**
	 * 
	 * @param unknown $template
	 * @return Ambigous <NULL, unknown>
	 */
	private function fetchTags($template)
	{
		$render_string = null;
		foreach($this->tag as $tags=>$seed)
		{
				foreach($seed as $html)
				{
					$render_string .=  $html;
				}
				 $template = preg_replace("/{{".$tags."}}/", $render_string, $template);
				 $render_string = null;
				 		
		}
		
		return $template;
	}
	
	/**
	 * Renders defined forms.
	 * @param unknown $template
	 * @return mixed
	 */
	private function renderForms($template)
	{
		
		foreach($this->form as $seed=>$content)
		{
			$build_string = $content['form'];
			foreach($content['element'] as $element)
			{
				$build_string .= "{{".$element."}}";
			}
			$build_string .="</form>";
			$template = preg_replace("/{{" . $seed . "}}/",$build_string,$template);
		}
		
		return $template;
	}
	
	/**
	 * This scans the template for {{!blocks}}{{/!blocks}}.
	 * Anything bewteen the block is defined as a new tag with the block name as the seed
	 * this way you can reuse the blocks by just inserting a regular seed/tag without the bang!
	 * The block is a CLONE of the previous block.  You may insert other tags or seeds into the block.
	 * This could allow for dynamic content for each block.
	 * @param unknown $template
	 * @return mixed
	 */
	private function scanForBlocks($template)
	
	{
		preg_match_all("/{{!(?P<var>.*?)}}(?P<content>(?:.|\r?\n)*?){{\/!(?P<endvar>.*?)}}/", $template, $output_array);
		
		foreach($output_array as $key=>$array)
		{
			foreach($array as $key2=> $value)
			{
				$this->tag[$output_array['var'][$key2]]=$output_array['content'][$key2];
			}
		}
		$template = $this->fetchTags($template);
		
		return $template;
	}
	
	/**
	 * Loads blocks from an external source.  Usefull for defining multiple blocks for reuse in other pages.
	 * @param unknown $path
	 */
	function loadBlocksFromFile($path)
	{
		$file = file_get_contents($path);
		$this->scanForBlocks($file);
	}
	
	
	/**
	 * This function returns a cached page (if it's set)
	 * Currently, cached pages are stored in the user $_SESSION variable.
	 * 
	 * @param string $path
	 * @return boolean
	 */
	private function getCachedPage($path)
	{
		if(function_exists('session_status'))
		{
			if ( session_status() == PHP_SESSION_NONE) {
   				return false;
			}
		}
		
		if(!empty($_SESSION['otter'][$path]))
		{
			return $_SESSION['otter'][$path];
		}
		
	}
	
	/**
	 * Renders all defined increments and returns the updated template.
	 * @param mixed $template
	 * @return mixed
	 */
	private function renderIncrements($template)
	{
		foreach($this->inc as $seed=>$content)
		{
			$count = $content['inc'];
			$start = $content['start'];
			$prefix = $content['prefix'];
			$num = $start;
			do{
				$template = preg_replace("/{{".$seed."}}/",$prefix . $num,$template,1,$preg_count);
				$num += $count;
			} while ($preg_count>0);
		}
		
		return $template;
	}
	
	/**
	 * Caches a page in the user $_SESSION variable
	 * @param string $path
	 * @param mixed $template
	 * @return boolean
	 */
	private function cachePage($path,$template)
	{
		if(function_exists('session_status'))
		{
			if ( session_status() == PHP_SESSION_NONE) {
				session_start();
			}
		}
		
		$_SESSION['otter'][$path] = $template;
		return true;
	}
	
	
	/**
	 * Renders all conditionals on template
	 * @param string $template
	 * @return mixed
	 */
	private function runConditionals($template)
	{
		$build_string = null;
		if(!empty($this->conditional))
		{
			foreach($this->conditional as $seed=>$content)
			{
				$template = preg_replace("/{{".$seed."}}/", $content, $template);
			}
		}
		
		
		return $template;
	}
	
	/**
	 * Echos a defined variable into the document/template
	 * @param unknown $template
	 * @return mixed
	 */
	private function echoVariables($template)
	{
		
		foreach($this->vars as $var=>$value)
		{
			echo $var;
			$pattern = "/{{%".$var."}}/";
			
			$template = preg_replace($pattern,$value,$template);
		}
		return $template;
	}
	
	/**
	 * Defines a variable or text.  Should be wrapped in tags in HTML to prevent white space echoing.
	 * @param string $seed
	 * @param string $value
	 */
	function addVariable($seed,$value)
	{
		$this->createTag('tag',$seed,$value);
		
	}
	
	/**
	 * Builds attributes for elements
	 * @param unknown $options
	 * @return Ambigous <NULL, string>
	 */
	private function innerTags($options)
	{
		$innerText = null;
		
		foreach($options as $tag=>$value)
		{
			$innerText .= " $tag='$value' ";
		}
		
		return $innerText;
	}
	
	
	/**
	 * Clears a specific or all cached pages
	 * @param unknown $filename
	 * @return boolean
	 */
	function clearCache($filename=null)
	{
		if(empty($filename))
		{
			unset($_SESSION['otter']);
			return true;
		}
		
		if(isset($_SESSION['otter'][$filename]))
		{
			unset($_SESSION['otter'][$filename]);
		}
		return true;
	}
	
	
	/**
	 * If set to TRUE, will use the cached page for the page this is called from.
	 * @param unknown $bool
	 */
	function useCache($bool)
	{
		$this->use_cache = $bool;
	}
	
	/**
	 * Returns the CSS array.
	 * Usefull for error checking
	 * @return multitype:
	 */
	function getCSS()
	{
		return $this->css;
	}
	
	/**
	 * Returns the Scripts array
	 * Useful for error checking
	 * @return multitype:
	 */
	function getScripts()
	{
		return $this->scripts;
	}
	
	/**
	 * Returns an array containing all defined tags.
	 * @return multitype:
	 */
	function listAllTags()
	{
		return $this->tag;
	}
	
}//end class
