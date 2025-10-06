<?php

class RifaController extends Controller
{
	private $args;

	public function __construct($args)
	{
		parent::__construct();
		$this->args = $args;
	}

	public function index()
	{
		$this->view->render('Rifa/index', [
			'pageTitle' => 'Rifas La Paz - Participa',
			"title" => "RIFAS LA PAZ"
		]);
	}
}
