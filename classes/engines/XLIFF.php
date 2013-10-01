<?php

class XLIFF extends XMLWriter {
	private $source_cc='';
	private $target_cc='';

	public function __construct($source, $target, $strict=false, $notes='') {
		$this->source_cc = $source;
		$this->target_cc = $target;
		$this->openMemory();
		$this->setIndent(true);
		$this->setIndentString(' ');
		$this->startDocument('1.0', 'UTF-8');

		$this->startElement('xliff');
		//$this->writeAttribute('version', '1.0');
		$this->writeAttribute('version', '1.2');
		$this->writeAttribute('xmlns', 'urn:oasis:names:tc:xliff:document:1.2');
		$this->writeAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
		$this->writeAttribute('xsi:schemaLocation', 'urn:oasis:names:tc:xliff:document:1.2 xliff-core-1.2-strict.xsd');

		$this->startElement('file');
		$this->writeAttribute('original', 'global');
		$this->writeAttribute('source-language', $this->source_cc);
		if($strict) $this->writeAttribute('target-language', $this->target_cc);
		$this->writeAttribute('datatype', 'plaintext');
		//$this->writeAttribute('datatype', 'xml');
		//$this->writeAttribute('date', date('c'));
		
		$this->writeAttribute('tool', 'PAGL');
		$this->writeAttribute('product-version', '4.5');
		
		$this->startElement('header');
		$this->startElement('phase-group');
		$this->startElement('phase');
		$this->writeAttribute('phase-name', 'extract');
		$this->writeAttribute('process-name', 'extraction');
		$this->startElement('note');
		$this->text($notes);
		$this->endElement(); //note
		$this->endElement(); //phase
		$this->endElement(); //phase group
		
		$this->startElement('skl');
		$this->startElement('external-file');
		$this->writeAttribute('href', 'http://www.pointandgolocalise.com/');
		$this->endElement(); //external-file
		$this->endElement(); //skl
		
		$this->endElement(); //header

		$this->startElement('body');
	}

	public function addPhrase($source, $target) {
		$this->startElement('trans-unit');
		$this->writeAttribute('id', md5($source));
		$this->startElement('source');
		$this->writeAttribute('xml:lang', $this->source_cc);
		$this->text($source);
		$this->endElement();
		$this->startElement('target');
		//$this->writeAttribute('xml:lang', $this->target_cc);
		$this->text($target);
		$this->endElement();
		$this->endElement();
	}
	
	public function addPhrases(array $sources, array $targets) {
		$this->startElement('trans-unit');
		$this->writeAttribute('id', md5(serialize($sources)));
		
		$this->startElement('source');
		$this->writeAttribute('xml:lang', $this->source_cc);
		$this->setIndent(false);
		foreach($sources as $source){
			$this->addG($source[1], $source[0]);
		}
		$this->endElement();
		$this->startElement('target');
		foreach($targets as $target){
			$this->addG($target[1], $target[0]);
		}
		$this->endElement();
		$this->setIndent(true);
		//Notes
		$this->startElement('note');
		$this->text('');
		$this->endElement();
		
		$this->endElement();
	}
	protected function addG($text, $id){
		$this->startElement('g');
		$this->writeAttribute('id', $id);
		$this->writeAttribute('clone', 'no');
		$this->text($text);
		$this->endElement();
	}
	/*
	protected function addPairTag($text, $id){
		//BPT
		$this->startElement('bpt');
		$this->writeAttribute('id', $id+10);
		$this->endElement();
		
		//TEXT
		$this->text($text);
		
		//EPT
		$this->startElement('ept');
		$this->writeAttribute('id', $id+10);
	}
	*/
	protected function addPlaceHoder($text, $id){
		$this->startElement('ph');
		$this->writeAttribute('id', $id);		
		//TEXT
		$this->text($text);

		$this->endElement();
	}

	//end 3 levels
	public function getDocument() {
		$this->endElement();
		$this->endElement();
		$this->endElement();
		$this->endDocument();
		return $this->outputMemory();
	}

	public function output() {
		header('Content-type: text/xml');
		echo $this->getDocument();
	}

}