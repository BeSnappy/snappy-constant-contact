<?php namespace Snappy\Apps\ConstantContact;

use Snappy\Apps\App as BaseApp;
use Snappy\Apps\ContactCreatedHandler;

class App extends BaseApp implements ContactCreatedHandler {

	/**
	 * The name of the application.
	 *
	 * @var string
	 */
	public $name = 'Constant Contact';

	/**
	 * The application description.
	 *
	 * @var string
	 */
	public $description = 'Automatically add new contacts to your Constant Contact mailing list.';

	/**
	 * Any notes about this application
	 *
	 * @var string
	 */
	public $notes = '';

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
		array('name' => 'key', 'type' => 'text', 'help' => 'Enter your Constant Contact API Key', 'validate' => 'required'),
		array('name' => 'token', 'type' => 'text', 'help' => 'Enter your Constant Contact Access Token', 'validate' => 'required'),
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
		$cc = new \Ctct\ConstantContact($this->config['key']);

		try
		{
			$response = $cc->getContactByEmail($this->config['token'], $contact['value']);

			if (empty($response->results))
			{
				// Pull out the list ID if we got a alphanumeric name...
				if ( ! is_numeric($this->config['list']))
				{
					$lists = $cc->getLists($this->config['token']);
					$listName = $this->config['list'];
					$list = array_first($lists, function($key, $value) use ($listName)
					{
						return $value->name == $listName;
					});
					if ($list)
					{
						$this->config['list'] = $list->id;
					}
					else
					{
						return;
					}
				}

				$constantContact = new \Ctct\Components\Contacts\Contact;

				$constantContact->addEmail($contact['value']);
				$constantContact->addList($this->config['list']);

				if (isset($contact['first_name']) and isset($contact['last_name']))
				{
					$constantContact->first_name = $contact['first_name'];
					$constantContact->last_name = $contact['last_name'];
				}

				$cc->addContact($this->config['token'], $constantContact, false);
			}
		}
		catch (\Exception $e)
		{
			call_user_func(\App::make('bugsnagger'), $e);
		}
	}

}
