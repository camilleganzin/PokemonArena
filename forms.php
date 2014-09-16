<?php

/**
* @Submit(value='Créer')
* @FormRenderer(id='my-new-pokemon-form')
*/
class CreateForm extends Former {

	/**
	* @InputField(title='Nom du Pokemon', values="nom")
	* @Required()
	* @LengthValidator(min=4,max=64)
	* @HtmlFilter()
	*/
	public $nom;

	/**
	* @SelectField(title='Famille du Pokemon', values='Pikachu|Carapuce|Salameche')
	* @Required()
	*/
	public $famille;

}
