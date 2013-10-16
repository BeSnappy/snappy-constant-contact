<?php namespace Snappy\Apps\ConstantContact;

use Snappy\Apps\App as BaseApp;
use Snappy\Apps\ContactCreatedHandler;

class App extends BaseApp {

	/**
	 * The name of the application.
	 *
	 * @var string
	 */
	public $name = 'ConstantContact';

	/**
	 * The application description.
	 *
	 * @var string
	 */
	public $description = 'Add new contacts to your Constant Contact mailing list.';

	/**
	 * Any notes about this application
	 *
	 * @var string
	 */
	public $notes = 'Constant Contact';

	/**
	 * The application's icon filename.
	 *
	 * @var string
	 */
	public $icon = 'constant_contact.png';

	/**
	 * The application author name.
	 *
	 * @var string
	 */
	public $author = 'UserScape, Inc.';

	/**
	 * The application author e-mail.
	 *
	 * @var string
	 */
	public $email = 'it@userscape.com';

	/**
	 * The settings required by the application.
	 *
	 * @var array
	 */
	public $settings = array(
		array('name' => 'token', 'type' => 'text', 'help' => 'Enter your Constant Contact API Token'),
		array('name' => 'list', 'type' => 'text', 'help' => 'Enter the mailing list that will receive the contacts'),
	);

	/**
	 * Handle the creation of a new contact.
	 *
	 * @param  array  $ticket
	 * @param  array  $contact
	 * @return void
	 */
	public function handleContactCreated(array $ticket, array $contact)
	{
		$cc = new \Ctct\ConstantContact($this->config['token']);

		try
		{
			$response = $cc->getContactByEmail($this->config['token'], $contact['value']);

			if (empty($response->results))
			{
				$contact = new \Ctct\Components\Contacts\Contact;

				$contact->addEmail($contact['value']);
				$contact->addList($this->config['list']);

				if (isset($contact['first_name']) and isset($contact['last_name']))
				{
					$contact->first_name = $contact['first_name'];
					$contact->last_name = $contact['last_name'];
				}

				$cc->addContact($this->config['token'], $contact, false);
			}
		}
		catch (\Exception $e)
		{
			call_user_func(\App::make('bugsnagger'), $e);
		}
	}

}