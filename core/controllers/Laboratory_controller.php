<?php

defined('_EXEC') or die;

include_once(PATH_MODELS . 'System_model.php');
require_once 'plugins/nexmo/vendor/autoload.php';

class Laboratory_controller extends Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	public function index($params)
    {
        if (Format::exist_ajax_request() == true)
		{
			if ($_POST['action'] == 'filter_custody_chains')
			{
				$filter = System::temporal('get', 'laboratory', 'filter');

				if ($_POST['filter'] == 'true')
				{
					$filter['account'] = (Session::get_value('vkye_user')['god'] == 'activate_and_wake_up') ? $_POST['account'] : '';
					$filter['deleted_status'] = (Session::get_value('vkye_user')['god'] == 'activate_and_wake_up') ? $_POST['deleted_status'] : '';
					$filter['type'] = ($params[0] == 'covid') ? $_POST['type'] : $params[0];
					$filter['start_date'] = $_POST['start_date'];
					$filter['end_date'] = $_POST['end_date'];
					$filter['start_hour'] = $_POST['start_hour'];
					$filter['end_hour'] = $_POST['end_hour'];
					$filter['sended_status'] = ($params[0] == 'covid') ? $_POST['sended_status'] : '';
				}
				else if ($_POST['filter'] == 'false')
					$filter = [];

				System::temporal('set_forced', 'laboratory', 'filter', $filter);

				echo json_encode([
					'status' => 'success',
					'message' => '{$lang.operation_success}'
				]);
			}

			if ($_POST['action'] == 'restore_custody_chain' OR $_POST['action'] == 'empty_custody_chains' OR $_POST['action'] == 'delete_custody_chain')
			{
				if ($_POST['action'] == 'restore_custody_chain')
					$query = $this->model->restore_custody_chain($_POST['id']);
				else if ($_POST['action'] == 'empty_custody_chains')
					$query = $this->model->empty_custody_chains();
				else if ($_POST['action'] == 'delete_custody_chain')
					$query = $this->model->delete_custody_chain($_POST['id']);

				if (!empty($query))
				{
					echo json_encode([
						'status' => 'success',
						'message' => '{$lang.operation_success}'
					]);
				}
				else
				{
					echo json_encode([
						'status' => 'error',
						'message' => '{$lang.operation_error}'
					]);
				}
			}
		}
		else
		{
			define('_title', Configuration::$web_page . ' | {$lang.laboratory} | {$lang.' . $params[0] . '}');

			// $this->model->sql();

			if (System::temporal('get_if_exists', 'laboratory', 'filter') == false)
				System::temporal('set_forced', 'laboratory', 'filter', []);

			global $global;

			$global['render'] = $params[0];
			$global['custody_chains'] = $this->model->read_custody_chains($params[0]);

			$template = $this->view->render($this, 'index');

			echo $template;
		}
    }

	public function control()
    {
        if (Format::exist_ajax_request() == true)
		{

		}
		else
		{
			define('_title', Configuration::$web_page . ' | Marbu Salud');

			$template = $this->view->render($this, 'marbu');

			echo $template;
		}
    }

	public function create($params)
	{
        $go = false;

        if (!empty($params[0]))
        {
            global $global;

            $global['type'] = $params[0];

            if (($global['type'] == 'alcoholic' OR $global['type'] == 'antidoping' OR $global['type'] == 'covid_pcr' OR $global['type'] == 'covid_an' OR $global['type'] == 'covid_ac') AND !empty($params[1]))
            {
				$global['employee'] = $this->model->read_employee($params[1]);

				if (!empty($global['employee']))
					$go = true;
            }
        }

		if ($go == true)
        {
            if (Format::exist_ajax_request() == true)
    		{
                if ($_POST['action'] == 'create_custody_chain')
                {
                    $errors = [];

					if (Validations::empty($_POST['reason']) == false)
    					array_push($errors, ['reason','{$lang.dont_leave_this_field_empty}']);

					if (($global['type'] == 'covid_pcr' OR $global['type'] == 'covid_an' OR $global['type'] == 'covid_ac') AND Validations::empty($_POST['start_process']) == false)
    					array_push($errors, ['start_process','{$lang.dont_leave_this_field_empty}']);

					if (($global['type'] == 'covid_pcr' OR $global['type'] == 'covid_an' OR $global['type'] == 'covid_ac') AND Validations::empty($_POST['end_process']) == false)
    					array_push($errors, ['end_process','{$lang.dont_leave_this_field_empty}']);

					if ($global['type'] == 'alcoholic' AND Validations::number(['int','float'], $_POST['test_1'], true) == false)
					   array_push($errors, ['test_1','{$lang.invalid_field}']);

					if ($global['type'] == 'alcoholic' AND Validations::number(['int','float'], $_POST['test_2'], true) == false)
					   array_push($errors, ['test_2','{$lang.invalid_field}']);

					if ($global['type'] == 'alcoholic' AND Validations::number(['int','float'], $_POST['test_3'], true) == false)
					   array_push($errors, ['test_3','{$lang.invalid_field}']);

					if (($global['custody_chain']['type'] == 'covid_pcr' OR $global['custody_chain']['type'] == 'covid_an') AND Validations::empty($_POST['test_result']) == false)
						array_push($errors, ['test_result','{$lang.dont_leave_this_field_empty}']);

					if (($global['custody_chain']['type'] == 'covid_pcr' OR $global['custody_chain']['type'] == 'covid_an') AND Validations::empty($_POST['test_unity']) == false)
						array_push($errors, ['test_unity','{$lang.dont_leave_this_field_empty}']);

					if (($global['custody_chain']['type'] == 'covid_pcr' OR $global['custody_chain']['type'] == 'covid_an') AND Validations::empty($_POST['test_reference_values']) == false)
						array_push($errors, ['test_reference_values','{$lang.dont_leave_this_field_empty}']);

					if ($global['custody_chain']['type'] == 'covid_ac' AND Validations::empty($_POST['test_igm_result']) == false)
						array_push($errors, ['test_igm_result','{$lang.dont_leave_this_field_empty}']);

					if ($global['custody_chain']['type'] == 'covid_ac' AND Validations::empty($_POST['test_igm_reference_values']) == false)
						array_push($errors, ['test_igm_reference_values','{$lang.dont_leave_this_field_empty}']);

					if ($global['custody_chain']['type'] == 'covid_ac' AND Validations::empty($_POST['test_igg_result']) == false)
						array_push($errors, ['test_igg_result','{$lang.dont_leave_this_field_empty}']);

					if ($global['custody_chain']['type'] == 'covid_ac' AND Validations::empty($_POST['test_igg_reference_values']) == false)
						array_push($errors, ['test_igg_reference_values','{$lang.dont_leave_this_field_empty}']);

                    if (Validations::empty($_POST['date']) == false)
    					array_push($errors, ['date','{$lang.dont_leave_this_field_empty}']);

                    if (Validations::empty($_POST['hour']) == false)
    					array_push($errors, ['hour','{$lang.dont_leave_this_field_empty}']);

                    if (Validations::empty($_POST['chemical']) == false)
    					array_push($errors, ['chemical','{$lang.dont_leave_this_field_empty}']);

    				if (empty($errors))
    				{
                        $_POST['employee'] = $global['employee']['id'];
                        $_POST['type'] = $global['type'];

						$query = $this->model->create_custody_chain($_POST);

    					if (!empty($query))
    					{
    						echo json_encode([
    							'status' => 'success',
    							'message' => '{$lang.operation_success}',
    							'path' => 'go_back'
    						]);
    					}
    					else
    					{
    						echo json_encode([
    							'status' => 'error',
    							'message' => '{$lang.operation_error}'
    						]);
    					}
    				}
    				else
    				{
    					echo json_encode([
    						'status' => 'error',
    						'errors' => $errors
    					]);
    				}
                }
    		}
    		else
    		{
    			define('_title', Configuration::$web_page . ' | {$lang.do_test} | {$lang.' . $params[0] . '}');

				$global['locations'] = $this->model->read_locations();

    			$template = $this->view->render($this, 'create');

    			echo $template;
    		}
        }
        else
            Permissions::redirection('laboratory');
	}

	public function update($params)
	{
		$go = false;

		if (!empty($params[0]))
		{
			global $global;

			$global['custody_chain'] = $this->model->read_custody_chain($params[0]);

			if (!empty($global['custody_chain']))
				$go = true;
		}

		if ($go == true)
		{
			if (Format::exist_ajax_request() == true)
			{
				if ($_POST['action'] == 'update_custody_chain')
				{
					$errors = [];

					if (($global['custody_chain']['type'] == 'covid_pcr' OR $global['custody_chain']['type'] == 'covid_an' OR $global['custody_chain']['type'] == 'covid_ac') AND empty($global['custody_chain']['employee']))
					{
						if (Validations::empty($_POST['firstname']) == false)
	    					array_push($errors, ['firstname','{$lang.dont_leave_this_field_empty}']);

						if (Validations::empty($_POST['lastname']) == false)
	    					array_push($errors, ['lastname','{$lang.dont_leave_this_field_empty}']);

						if (Validations::empty($_POST['ife']) == false)
	    					array_push($errors, ['ife','{$lang.dont_leave_this_field_empty}']);

						if (Validations::empty($_POST['birth_date']) == false)
					   		array_push($errors, ['birth_date','{$lang.dont_leave_this_field_empty}']);

						if (Validations::empty($_POST['age']) == false)
	    					array_push($errors, ['age','{$lang.dont_leave_this_field_empty}']);
						else if (Validations::number('int', $_POST['age']) == false)
						   array_push($errors, ['age','{$lang.invalid_field}']);

					   	if (Validations::empty($_POST['sex']) == false)
					   		array_push($errors, ['sex','{$lang.dont_leave_this_field_empty}']);

					   	if (Validations::empty($_POST['email']) == false)
					   		array_push($errors, ['email','{$lang.dont_leave_this_field_empty}']);
					   	else if (Validations::email($_POST['email']) == false)
					   		array_push($errors, ['email','{$lang.invalid_field}']);

						if (Validations::empty([$_POST['phone_country'],$_POST['phone_number']]) == false)
			                array_push($errors, ['phone_number','{$lang.dont_leave_this_field_empty}']);
						else if (Validations::number('int', $_POST['phone_number']) == false)
							array_push($errors, ['phone_number','{$lang.invalid_field}']);

						if (Validations::empty($_POST['travel_to']) == false)
						   array_push($errors, ['travel_to','{$lang.dont_leave_this_field_empty}']);

						if (Validations::empty($_POST['lang']) == false)
						   array_push($errors, ['lang','{$lang.dont_leave_this_field_empty}']);
					}

    				if (Validations::empty($_POST['reason']) == false)
    					array_push($errors, ['reason','{$lang.dont_leave_this_field_empty}']);

					if (($global['custody_chain']['type'] == 'covid_pcr' OR $global['custody_chain']['type'] == 'covid_an' OR $global['custody_chain']['type'] == 'covid_ac') AND Validations::empty($_POST['start_process']) == false)
    					array_push($errors, ['start_process','{$lang.dont_leave_this_field_empty}']);

					if (($global['custody_chain']['type'] == 'covid_pcr' OR $global['custody_chain']['type'] == 'covid_an' OR $global['custody_chain']['type'] == 'covid_ac') AND Validations::empty($_POST['end_process']) == false)
    					array_push($errors, ['end_process','{$lang.dont_leave_this_field_empty}']);

					if ($global['custody_chain']['type'] == 'alcoholic' AND Validations::number(['int','float'], $_POST['test_1'], true) == false)
					   array_push($errors, ['test_1','{$lang.invalid_field}']);

					if ($global['custody_chain']['type'] == 'alcoholic' AND Validations::number(['int','float'], $_POST['test_2'], true) == false)
					   array_push($errors, ['test_2','{$lang.invalid_field}']);

					if ($global['custody_chain']['type'] == 'alcoholic' AND Validations::number(['int','float'], $_POST['test_3'], true) == false)
					   array_push($errors, ['test_3','{$lang.invalid_field}']);

					if (($global['custody_chain']['type'] == 'covid_pcr' OR $global['custody_chain']['type'] == 'covid_an') AND Validations::empty($_POST['test_result']) == false)
						array_push($errors, ['test_result','{$lang.dont_leave_this_field_empty}']);

					if (($global['custody_chain']['type'] == 'covid_pcr' OR $global['custody_chain']['type'] == 'covid_an') AND Validations::empty($_POST['test_unity']) == false)
						array_push($errors, ['test_unity','{$lang.dont_leave_this_field_empty}']);

					if (($global['custody_chain']['type'] == 'covid_pcr' OR $global['custody_chain']['type'] == 'covid_an') AND Validations::empty($_POST['test_reference_values']) == false)
						array_push($errors, ['test_reference_values','{$lang.dont_leave_this_field_empty}']);

					if ($global['custody_chain']['type'] == 'covid_ac' AND Validations::empty($_POST['test_igm_result']) == false)
						array_push($errors, ['test_igm_result','{$lang.dont_leave_this_field_empty}']);

					if ($global['custody_chain']['type'] == 'covid_ac' AND Validations::empty($_POST['test_igm_reference_values']) == false)
						array_push($errors, ['test_igm_reference_values','{$lang.dont_leave_this_field_empty}']);

					if ($global['custody_chain']['type'] == 'covid_ac' AND Validations::empty($_POST['test_igg_result']) == false)
						array_push($errors, ['test_igg_result','{$lang.dont_leave_this_field_empty}']);

					if ($global['custody_chain']['type'] == 'covid_ac' AND Validations::empty($_POST['test_igg_reference_values']) == false)
						array_push($errors, ['test_igg_reference_values','{$lang.dont_leave_this_field_empty}']);

                    if (Validations::empty($_POST['date']) == false)
    					array_push($errors, ['date','{$lang.dont_leave_this_field_empty}']);

                    if (Validations::empty($_POST['hour']) == false)
    					array_push($errors, ['hour','{$lang.dont_leave_this_field_empty}']);

                    if (Validations::empty($_POST['chemical']) == false)
    					array_push($errors, ['chemical','{$lang.dont_leave_this_field_empty}']);

    				if (empty($errors))
    				{
						if (($global['custody_chain']['type'] == 'covid_pcr' OR $global['custody_chain']['type'] == 'covid_an' OR $global['custody_chain']['type'] == 'covid_ac') AND empty($global['custody_chain']['employee']))
						{
							if ($global['custody_chain']['account_path'] != 'moonpalace')
								$_POST['qr']['filename'] = 'covid_qr_' . $global['custody_chain']['token'] . '_' . Dates::current_date('Y_m_d') . '_' . Dates::current_hour('H_i_s') . '.png';

							$_POST['pdf']['filename'] = 'covid_pdf_' . $global['custody_chain']['token'] . '_' . Dates::current_date('Y_m_d') . '_' . Dates::current_hour('H_i_s') . '.pdf';
						}

						$_POST['custody_chain'] = $global['custody_chain'];

						$query = $this->model->update_custody_chain($_POST);

    					if (!empty($query))
    					{
							if (($global['custody_chain']['type'] == 'covid_pcr' OR $global['custody_chain']['type'] == 'covid_an' OR $global['custody_chain']['type'] == 'covid_ac') AND empty($global['custody_chain']['employee']) AND $_POST['save'] == 'save_and_send' AND $global['custody_chain']['account_path'] != 'moonpalace')
							{
								$mail = new Mailer(true);

								try
								{
									$mail->setFrom(Configuration::$vars['marbu']['email'], 'Marbu Salud');
									$mail->addAddress($_POST['email'], $_POST['firstname'] . ' ' . $_POST['lastname']);
									$mail->addAttachment(PATH_UPLOADS . $_POST['pdf']['filename']);
									$mail->Subject = '¡' . Languages::email('hi')[$_POST['lang']] . ' ' . explode(' ',  $_POST['firstname'])[0] . '! ' . Languages::email('your_results_are_ready')[$_POST['lang']];
									$mail->Body =
									'<html>
										<head>
											<title>' . $mail->Subject . '</title>
										</head>
										<body>
											<table style="width:100%;max-width:600px;margin:0px;padding:0px;border:0px;background-color:#004770;">
												<tr style="width:100%;margin:0px;padding:0px;border:0px;">
													<td style="width:100px;margin:0px;padding:20px 0px 20px 20px;border:0px;box-sizing:border-box;vertical-align:middle;">
														<img style="width:100px" src="https://' . Configuration::$domain . '/images/marbu_logotype_color_circle.png">
													</td>
													<td style="width:auto;margin:0px;padding:20px;border:0px;box-sizing:border-box;vertical-align:middle;">
														<table style="width:100%;margin:0px;padding:0px;border:0px;">
															<tr style="width:100%;margin:0px;padding:0px;border:0px;">
																<td style="width:100%;margin:0px;padding:0px;border:0px;font-size:12px;font-weight:600;text-align:right;color:#fff;">Marbu Salud S.A. de C.V.</td>
															</tr>
															<tr style="width:100%;margin:0px;padding:0px;border:0px;">
																<td style="width:100%;margin:0px;padding:0px;border:0px;font-size:12px;font-weight:400;text-align:right;color:#fff;">MSA1907259GA</td>
															</tr>
															<tr style="width:100%;margin:0px;padding:0px;border:0px;">
																<td style="width:100%;margin:0px;padding:0px;border:0px;font-size:12px;font-weight:400;text-align:right;color:#fff;">Av. Del Sol SM47 M6 L21 Planta Alta</td>
															</tr>
															<tr style="width:100%;margin:0px;padding:0px;border:0px;">
																<td style="width:100%;margin:0px;padding:0px;border:0px;font-size:12px;font-weight:400;text-align:right;color:#fff;">CP: 77506 Cancún, Qroo. México</td>
															</tr>
														</table>
													</td>
												</tr>
											</table>
											<table style="width:100%;max-width:600px;margin:20px 0px;padding:0px;border:1px dashed #000;box-sizing:border-box;background-color:#fff;">
												<tr style="width:100%;margin:0px;padding:0px;border:0px;">
													<td style="width:100%;margin:0px;padding:20px 20px 0px 20px;border:0px;box-sizing:border-box;font-size:18px;font-weight:600;text-align:center;text-transform:uppercase;color:#000;">¡' . Languages::email('ready_results')[$_POST['lang']] . '!</td>
												</tr>
												<tr style="width:100%;margin:0px;padding:0px;border:0px;">
													<td style="width:100%;margin:0px;padding:20px 20px 0px 20px;border:0px;box-sizing:border-box;font-size:12px;font-weight:400;text-align:center;color:#757575;">¡' . Languages::email('hi')[Session::get_value('vkye_lang')] . ' <strong>' . explode(' ', $_POST['firstname'])[0] . '</strong>! ' . Languages::email('get_covid_results_1')[$_POST['lang']] . ' <strong>' . Dates::format_date($global['custody_chain']['date'], 'short') . '</strong> ' . Languages::email('get_covid_results_2')[$_POST['lang']] . '</td>
												</tr>
												<tr style="width:100%;margin:0px;padding:0px;border:0px;">
													<td style="width:100%;margin:0px;padding:20px 20px 0px 20px;border:0px;box-sizing:border-box;">
														<a style="width:100%;display:block;margin:0px;padding:10px;border:1px solid #bdbdbd;border-radius:5px;box-sizing:border-box;background-color:#fff;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#757575;" href="https://api.whatsapp.com/send?phone=' . Configuration::$vars['marbu']['phone'] . '&text=Hola, soy ' . $_POST['firstname'] . ' ' . $_POST['lastname'] . '. Mi folio es: ' . $global['custody_chain']['token'] . '. ">' . Languages::email('whatsapp_us_to_support')[$_POST['lang']] . '</a>
													</td>
												</tr>
												<tr style="width:100%;margin:0px;padding:0px;border:0px;">
													<td style="width:100%;margin:0px;padding:20px 20px 0px 20px;border:0px;box-sizing:border-box;">
														<img style="width:100%;" src="https://' . Configuration::$domain . '/uploads/' . $_POST['qr']['filename'] . '">
													</td>
												</tr>
												<tr style="width:100%;margin:0px;padding:0px;border:0px;">
													<td style="width:100%;margin:0px;padding:20px;border:0px;box-sizing:border-box;">
														<a style="width:100%;display:block;margin:0px;padding:10px;border:0px;border-radius:5px;box-sizing:border-box;background-color:#009688;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#fff;" href="https://' . Configuration::$domain . '/' . Session::get_value('vkye_account')['path'] . '/covid/' . $global['custody_chain']['token'] . '">' . Languages::email('view_online_results')[$_POST['lang']] . '</a>
													</td>
												</tr>
											</table>
											<table style="width:100%;max-width:600px;margin:0px;padding:0px;border:0px;background-color:#0b5178;">
												<tr style="width:100%;margin:0px;padding:0px;border:0px;">
													<td style="width:100%;margin:0px;padding:20px 20px 0px 20px;border:0px;box-sizing:border-box;font-size:12px;font-weight:400;text-align:left;color:#fff;"><a style="text-decoration:none;color:#fff;" href="tel:' . Configuration::$vars['marbu']['phone'] . '">' . Configuration::$vars['marbu']['phone'] . '</a></td>
												</tr>
												<tr style="width:100%;margin:0px;padding:0px;border:0px;">
													<td style="width:100%;margin:0px;padding:0px 20px;border:0px;box-sizing:border-box;font-size:12px;font-weight:400;text-align:left;color:#fff;"><a style="text-decoration:none;color:#fff;" href="mailto:' . Configuration::$vars['marbu']['email'] . '">' . Configuration::$vars['marbu']['email'] . '</a></td>
												</tr>
												<tr style="width:100%;margin:0px;padding:0px;border:0px;">
													<td style="width:100%;margin:0px;padding:0px 20px 20px 20px;border:0px;box-sizing:border-box;font-size:12px;font-weight:400;text-align:left;color:#fff;"><a style="text-decoration:none;color:#fff;" href="https://' . Configuration::$vars['marbu']['website'] . '">' . Configuration::$vars['marbu']['website'] . '</a></td>
												</tr>
											</table>
											<table style="width:100%;max-width:600px;margin:0px;padding:0px;border:0px;background-color:#004770;">
												<tr style="width:100%;margin:0px;padding:0px;border:0px;">
													<td style="width:100%;margin:0px;padding:20px 20px 0px 20px;border:0px;box-sizing:border-box;font-size:12px;font-weight:400;text-align:left;color:#fff;">' . Languages::email('power_by')[$_POST['lang']] . ' <a style="font-weight:600;text-decoration:none;color:#fff;" href="https://id.one-consultores.com">' . Configuration::$web_page . ' ' . Configuration::$web_version . '</a></td>
												</tr
												<tr style="width:100%;margin:0px;padding:0px;border:0px;">
													<td style="width:100%;margin:0px;padding:0px 20px;border:0px;box-sizing:border-box;font-size:12px;font-weight:400;text-align:left;color:#fff;">Copyright (C) <a style="text-decoration:none;color:#fff;" href="https://one-consultores.com">One Consultores</a></td>
												</tr>
												<tr style="width:100%;margin:0px;padding:0px;border:0px;">
													<td style="width:100%;margin:0px;padding:0px 20px 20px 20px;border:0px;box-sizing:border-box;font-size:12px;font-weight:400;text-align:left;color:#fff;">Software ' . Languages::email('development_by')[$_POST['lang']] . ' <a style="text-decoration:none;color:#fff;" href="https://codemonkey.com.mx">Code Monkey</a></td>
												</tr>
											</table>
										</body>
									</html>';
									$mail->send();
								}
								catch (Exception $e) {}

								$sms = new \Nexmo\Client\Credentials\Basic('51db0b68', 'd2TTUheuHp6BqYep');
								$sms = new \Nexmo\Client($sms);

								try
								{
									$sms->message()->send([
										'to' => $_POST['phone_country'] . $_POST['phone_number'],
										'from' => 'Marbu Salud',
										'text' => '¡' . Languages::email('hi')[$_POST['lang']] . ' ' . explode(' ',  $_POST['firstname'])[0] . '! ' . Languages::email('your_results_are_ready')[$_POST['lang']] . '. ' . Languages::email('we_send_email_1')[Session::get_value('vkye_lang')] . ' ' . $_POST['email'] . ' ' . Languages::email('we_send_email_3')[Session::get_value('vkye_lang')] . ': https://' . Configuration::$domain . '/' . Session::get_value('vkye_account')['path'] . '/covid/' . $global['custody_chain']['token'] . '. ' . Languages::email('power_by')[Session::get_value('vkye_lang')] . ' ' . Configuration::$web_page . ' ' . Configuration::$web_version . '.'
									]);
								}
								catch (Exception $e) {}
							}

    						echo json_encode([
    							'status' => 'success',
    							'message' => '{$lang.operation_success}',
								'path' => (($global['custody_chain']['type'] == 'covid_pcr' OR $global['custody_chain']['type'] == 'covid_an' OR $global['custody_chain']['type'] == 'covid_ac') AND empty($global['custody_chain']['employee'])) ? '/laboratory/covid' : 'go_back'
    						]);
    					}
    					else
    					{
    						echo json_encode([
    							'status' => 'error',
    							'message' => '{$lang.operation_error}'
    						]);
    					}
    				}
    				else
    				{
    					echo json_encode([
    						'status' => 'error',
    						'errors' => $errors
    					]);
    				}
				}
			}
			else
			{
				define('_title', Configuration::$web_page . ' | {$lang.update_test} | ' . $params[0]);

				$global['locations'] = $this->model->read_locations();

				$template = $this->view->render($this, 'update');

				echo $template;
			}
		}
	}

	public function authentication($params)
	{
		global $global;

		$global['laboratory'] = $this->model->read_laboratory($params[0]);
		$global['collector'] = $this->model->read_collector($params[1]);

		if (!empty($global['laboratory']) AND !empty($global['collector']))
		{
			if (Format::exist_ajax_request() == true)
	        {
				if ($_POST['action'] == 'create_authentication')
				{
					$errors = [];

					if (Validations::empty($_POST['type']) == false)
						array_push($errors, ['type','{$lang.dont_leave_this_field_empty}']);

					if (Validations::empty($_POST['taker']) == false)
						array_push($errors, ['taker','{$lang.dont_leave_this_field_empty}']);

					if (empty($errors))
					{
						$_POST['id'] = $global['collector']['id'];

						$query = $this->model->create_authentication($_POST);

						if (!empty($query))
						{
							echo json_encode([
								'status' => 'success',
								'message' => '{$lang.operation_success}'
							]);
						}
						else
						{
							echo json_encode([
								'status' => 'error',
								'message' => '{$lang.operation_error}'
							]);
						}
					}
					else
					{
						echo json_encode([
							'status' => 'error',
							'errors' => $errors
						]);
					}
				}

				if ($_POST['action'] == 'delete_authentication')
				{
					$query = $this->model->delete_authentication($global['collector']['id']);

					if (!empty($query))
					{
						echo json_encode([
							'status' => 'success',
							'message' => '{$lang.operation_success}'
						]);
					}
					else
					{
						echo json_encode([
							'status' => 'error',
							'message' => '{$lang.operation_error}'
						]);
					}
				}
	        }
	        else
	        {
	            define('_title', $global['laboratory']['name'] . ' | {$lang.authentication}');

				if ($global['laboratory']['blocked'] == true)
					$global['render'] = 'laboratory_blocked';
				else if ($global['collector']['blocked'] == true)
					$global['render'] = 'collector_blocked';
				else if (!in_array($global['laboratory']['id'], $global['collector']['laboratories']))
					$global['render'] = 'out_of_laboratory';
				else if (Dates::current_hour() < $global['collector']['schedule']['open'] OR Dates::current_hour() > $global['collector']['schedule']['close'])
					$global['render'] = 'out_of_time';
				else if (Dates::current_hour() > $global['collector']['schedule']['open'] AND Dates::current_hour() < $global['collector']['schedule']['close'])
				{
					$global['render'] = 'go';
					$global['takers'] = $this->model->read_takers();
				}

	            $template = $this->view->render($this, 'authentication');

	            echo $template;
	        }
		}
	}

	public function record($params)
    {
		global $global;

		$global['laboratory'] = $this->model->read_laboratory($params[0]);
		$global['collector'] = $this->model->read_collector($params[1]);

		if (!empty($global['laboratory']) AND !empty($global['collector']))
		{
			if (Format::exist_ajax_request() == true)
			{
				if ($_POST['action'] == 'record')
				{
					if (isset($_POST['accept_terms']))
					{
						$errors = [];

			            if (Validations::empty($_POST['firstname']) == false)
			                array_push($errors, ['firstname','{$lang.dont_leave_this_field_empty}']);

			            if (Validations::empty($_POST['lastname']) == false)
			                array_push($errors, ['lastname','{$lang.dont_leave_this_field_empty}']);

			            if (Validations::empty($_POST['birth_date_year']) == false)
			                array_push($errors, ['birth_date_year','{$lang.dont_leave_this_field_empty}']);

			            if (Validations::empty($_POST['birth_date_month']) == false)
			                array_push($errors, ['birth_date_month','{$lang.dont_leave_this_field_empty}']);

			            if (Validations::empty($_POST['birth_date_day']) == false)
			                array_push($errors, ['birth_date_day','{$lang.dont_leave_this_field_empty}']);

			            if (Validations::empty($_POST['age']) == false)
			                array_push($errors, ['age','{$lang.dont_leave_this_field_empty}']);
			            else if (Validations::number('int', $_POST['age']) == false)
			                array_push($errors, ['age','{$lang.invalid_field}']);

			            if (Validations::empty($_POST['sex']) == false)
			                array_push($errors, ['sex','{$lang.dont_leave_this_field_empty}']);

						if (Validations::empty($_POST['ife']) == false)
			                array_push($errors, ['ife','{$lang.dont_leave_this_field_empty}']);

						if (Validations::empty($_POST['email']) == false)
							array_push($errors, ['email','{$lang.dont_leave_this_field_empty}']);
						else if (Validations::email($_POST['email']) == false)
							array_push($errors, ['email','{$lang.invalid_field}']);

						if (Validations::empty([$_POST['phone_country'],$_POST['phone_number']]) == false)
							array_push($errors, ['phone_number','{$lang.dont_leave_this_field_empty}']);
						else if (Validations::number('int', $_POST['phone_number']) == false)
							array_push($errors, ['phone_number','{$lang.invalid_field}']);

						if (Validations::empty($_POST['type']) == false)
							array_push($errors, ['type','{$lang.dont_leave_this_field_empty}']);

			            if (empty($errors))
			            {
			                $_POST['token'] = System::generate_random_string();
							$_POST['firstname'] = ucwords($_POST['firstname']);
							$_POST['lastname'] = ucwords($_POST['lastname']);
							$_POST['birth_date'] = $_POST['birth_date_year'] . '-' . $_POST['birth_date_month'] . '-' . $_POST['birth_date_day'];
							$_POST['email'] = strtolower($_POST['email']);
							$_POST['qr']['filename'] = 'covid_qr_' . $_POST['token'] . '_' . Dates::current_date('Y_m_d') . '_' . Dates::current_hour('H_i_s') . '.png';
							$_POST['laboratory'] = $global['laboratory'];
							$_POST['collector'] = $global['collector'];

			                $query = $this->model->create_custody_chain($_POST, true);

			                if (!empty($query))
			                {
			                    System::temporal('set_forced', 'record', 'covid', $_POST);

								// $mail1 = new Mailer(true);
								//
								// try
								// {
								// 	$mail1->setFrom($global['laboratory']['email'], $global['laboratory']['name']);
								// 	$mail1->addAddress($_POST['email'], $_POST['firstname'] . ' ' . $_POST['lastname']);
								// 	$mail1->Subject = '¡' . Languages::email('hi')[Session::get_value('vkye_lang')] . ' ' . explode(' ',  $_POST['firstname'])[0] . '! ' . Languages::email('your_token_is')[Session::get_value('vkye_lang')] . ': ' . $_POST['token'];
								// 	$mail1->Body =
								// 	'<html>
								// 		<head>
								// 			<title>' . $mail1->Subject . '</title>
								// 		</head>
								// 		<body>
								// 			<table style="width:100%;max-width:600px;margin:0px;padding:0px;border:0px;background-color:' . $global['laboratory']['colors']['first'] . ';">
								// 				<tr style="width:100%;margin:0px;padding:0px;border:0px;">
								// 					<td style="width:100px;margin:0px;padding:20px 0px 20px 20px;border:0px;box-sizing:border-box;vertical-align:middle;">
								// 						<img style="width:100px" src="https://' . Configuration::$domain . '/uploads/' . $global['laboratory']['avatar'] . '">
								// 					</td>
								// 					<td style="width:auto;margin:0px;padding:20px;border:0px;box-sizing:border-box;vertical-align:middle;">
								// 						<table style="width:100%;margin:0px;padding:0px;border:0px;">
								// 							<tr style="width:100%;margin:0px;padding:0px;border:0px;">
								// 								<td style="width:100%;margin:0px;padding:0px;border:0px;font-size:12px;font-weight:600;text-align:right;color:#fff;">' . $global['laboratory']['business'] . '</td>
								// 							</tr>
								// 							<tr style="width:100%;margin:0px;padding:0px;border:0px;">
								// 								<td style="width:100%;margin:0px;padding:0px;border:0px;font-size:12px;font-weight:400;text-align:right;color:#fff;">' . $global['laboratory']['rfc'] . '</td>
								// 							</tr>
								// 							<tr style="width:100%;margin:0px;padding:0px;border:0px;">
								// 								<td style="width:100%;margin:0px;padding:0px;border:0px;font-size:12px;font-weight:400;text-align:right;color:#fff;">' . $global['laboratory']['address']['first'] . '</td>
								// 							</tr>
								// 							<tr style="width:100%;margin:0px;padding:0px;border:0px;">
								// 								<td style="width:100%;margin:0px;padding:0px;border:0px;font-size:12px;font-weight:400;text-align:right;color:#fff;">' . $global['laboratory']['address']['second'] . '</td>
								// 							</tr>
								// 						</table>
								// 					</td>
								// 				</tr>
								// 			</table>
								// 			<table style="width:100%;max-width:600px;margin:20px 0px;padding:0px;border:1px dashed #000;box-sizing:border-box;background-color:#fff;">
								// 				<tr style="width:100%;margin:0px;padding:0px;border:0px;">
								// 					<td style="width:100%;margin:0px;padding:20px 20px 0px 20px;border:0px;box-sizing:border-box;font-size:18px;font-weight:600;text-align:center;text-transform:uppercase;color:#000;">' . Languages::email('your_token_is')[Session::get_value('vkye_lang')] . ': ' . $_POST['token'] . '</td>
								// 				</tr>
								// 				<tr style="width:100%;margin:0px;padding:0px;border:0px;">
								// 					<td style="width:100%;margin:0px;padding:20px 20px 0px 20px;border:0px;box-sizing:border-box;font-size:12px;font-weight:400;text-align:center;color:#757575;">¡' . Languages::email('hi')[Session::get_value('vkye_lang')] . ' <strong>' . explode(' ', $_POST['firstname'])[0] . '</strong>! ' . Languages::email('your_results_next_email')[Session::get_value('vkye_lang')] . '</td>
								// 				</tr>
								// 				<tr style="width:100%;margin:0px;padding:0px;border:0px;">
								// 					<td style="width:100%;margin:0px;padding:20px 20px 0px 20px;border:0px;box-sizing:border-box;">
								// 						<img style="width:100%;" src="https://' . Configuration::$domain . '/uploads/' . $_POST['qr']['filename'] . '">
								// 					</td>
								// 				</tr>
								// 				<tr style="width:100%;margin:0px;padding:0px;border:0px;">
								// 					<td style="width:100%;margin:0px;padding:20px;border:0px;box-sizing:border-box;">
								// 						<a style="width:100%;display:block;margin:0px;padding:10px;border:0px;border-radius:5px;box-sizing:border-box;background-color:#009688;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#fff;" href="https://' . Configuration::$domain . '/' . $global['laboratory']['path'] . '/results/' . $_POST['token'] . '">' . Languages::email('view_online_results')[Session::get_value('vkye_lang')] . '</a>
								// 					</td>
								// 				</tr>
								// 			</table>
								// 			<table style="width:100%;max-width:600px;margin:0px;padding:0px;border:0px;background-color:' . $global['laboratory']['colors']['second'] . ';">
								// 				<tr style="width:100%;margin:0px;padding:0px;border:0px;">
								// 					<td style="width:100%;margin:0px;padding:20px 20px 0px 20px;border:0px;box-sizing:border-box;font-size:12px;font-weight:400;text-align:left;color:#fff;"><a style="text-decoration:none;color:#fff;" href="tel:' . $global['laboratory']['phone'] . '">' . $global['laboratory']['phone'] . '</a></td>
								// 				</tr>
								// 				<tr style="width:100%;margin:0px;padding:0px;border:0px;">
								// 					<td style="width:100%;margin:0px;padding:0px 20px;border:0px;box-sizing:border-box;font-size:12px;font-weight:400;text-align:left;color:#fff;"><a style="text-decoration:none;color:#fff;" href="mailto:' . $global['laboratory']['email'] . '">' . $global['laboratory']['email'] . '</a></td>
								// 				</tr>
								// 				<tr style="width:100%;margin:0px;padding:0px;border:0px;">
								// 					<td style="width:100%;margin:0px;padding:0px 20px 20px 20px;border:0px;box-sizing:border-box;font-size:12px;font-weight:400;text-align:left;color:#fff;"><a style="text-decoration:none;color:#fff;" href="https://' . $global['laboratory']['website'] . '">' . $global['laboratory']['website'] . '</a></td>
								// 				</tr>
								// 			</table>
								// 			<table style="width:100%;max-width:600px;margin:0px;padding:0px;border:0px;background-color:' . $global['laboratory']['colors']['first'] . ';">
								// 				<tr style="width:100%;margin:0px;padding:0px;border:0px;">
								// 					<td style="width:100%;margin:0px;padding:20px 20px 0px 20px;border:0px;box-sizing:border-box;font-size:12px;font-weight:400;text-align:left;color:#fff;">' . Languages::email('power_by')[Session::get_value('vkye_lang')] . ' <a style="font-weight:600;text-decoration:none;color:#fff;" href="https://id.one-consultores.com">' . Configuration::$web_page . ' ' . Configuration::$web_version . '</a></td>
								// 				</tr
								// 				<tr style="width:100%;margin:0px;padding:0px;border:0px;">
								// 					<td style="width:100%;margin:0px;padding:0px 20px;border:0px;box-sizing:border-box;font-size:12px;font-weight:400;text-align:left;color:#fff;">Copyright (C) <a style="text-decoration:none;color:#fff;" href="https://one-consultores.com">One Consultores</a></td>
								// 				</tr>
								// 				<tr style="width:100%;margin:0px;padding:0px;border:0px;">
								// 					<td style="width:100%;margin:0px;padding:0px 20px 20px 20px;border:0px;box-sizing:border-box;font-size:12px;font-weight:400;text-align:left;color:#fff;">Software ' . Languages::email('development_by')[Session::get_value('vkye_lang')] . ' <a style="text-decoration:none;color:#fff;" href="https://codemonkey.com.mx">Code Monkey</a></td>
								// 				</tr>
								// 			</table>
								// 		</body>
								// 	</html>';
								// 	$mail1->send();
								// }
								// catch (Exception $e) {}
								//
								// $sms = new \Nexmo\Client\Credentials\Basic('51db0b68', 'd2TTUheuHp6BqYep');
								// $sms = new \Nexmo\Client($sms);
								//
								// try
								// {
								// 	$sms->message()->send([
								// 		'to' => $_POST['phone_country'] . $_POST['phone_number'],
								// 		'from' => $global['laboratory']['name'],
								// 		'text' => '¡' . Languages::email('hi')[Session::get_value('vkye_lang')] . ' ' . explode(' ',  $_POST['firstname'])[0] . '! ' . Languages::email('your_token_is')[Session::get_value('vkye_lang')] . ': ' . $_POST['token'] . '. ' . Languages::email('we_send_email_1')[Session::get_value('vkye_lang')] . ' ' . $_POST['email'] . ' ' . Languages::email('we_send_email_2')[Session::get_value('vkye_lang')] . ': https://' . Configuration::$domain . '/' . $global['laboratory']['path'] . '/results/' . $_POST['token'] . '. ' . Languages::email('power_by')[Session::get_value('vkye_lang')] . ' ' . Configuration::$web_page . ' ' . Configuration::$web_version . '.'
								// 	]);
								// }
								// catch (Exception $e) {}

			                    echo json_encode([
			                        'status' => 'success',
			                        'message' => '{$lang.operation_success}'
			                    ]);
			                }
			                else
			                {
			                    echo json_encode([
			                        'status' => 'error',
			                        'message' => '{$lang.operation_error}'
			                    ]);
			                }
			            }
			            else
			            {
			                echo json_encode([
			                    'status' => 'error',
			                    'errors' => $errors
			                ]);
			            }
					}
					else
					{
						echo json_encode([
							'status' => 'error',
							'message' => '{$lang.accept_terms_error}'
						]);
					}
				}

				if ($_POST['action'] == 'restore_record')
				{
					System::temporal('set_forced', 'record', 'covid', []);

					echo json_encode([
						'status' => 'success',
						'message' => '{$lang.operation_success}'
					]);
				}
			}
			else
			{
				define('_title', $global['laboratory']['name'] . ' | {$lang.record}');

				if ($global['laboratory']['blocked'] == true)
					$global['render'] = 'laboratory_blocked';
				else if ($global['collector']['blocked'] == true)
					$global['render'] = 'collector_blocked';
				else if (!in_array($global['laboratory']['id'], $global['collector']['laboratories']))
					$global['render'] = 'out_of_laboratory';
				else if (Dates::current_hour() < $global['collector']['schedule']['open'] OR Dates::current_hour() > $global['collector']['schedule']['close'])
					$global['render'] = 'out_of_time';
				else if ($global['collector']['authentication']['type'] == 'none')
					$global['render'] = 'out_of_authentication';
				else if (Dates::current_hour() > $global['collector']['schedule']['open'] AND Dates::current_hour() < $global['collector']['schedule']['close'] AND ($global['collector']['authentication']['type'] == 'alcoholic' OR $global['collector']['authentication']['type'] == 'antidoping' OR $global['collector']['authentication']['type'] == 'covid'))
				{
					if (!empty($params[2]))
					{
						if ($global['collector']['authentication']['type'] == 'covid' AND System::temporal('get_if_exists', 'record', 'covid') == false)
							System::temporal('set_forced', 'record', 'covid', []);

						$global['render'] = 'go';
					}
					else
						header('Location: https://' . Configuration::$domain . '/' . $global['laboratory']['path'] . '/record/' . $global['collector']['token'] . '/' . $global['collector']['authentication']['type']);
				}

				$template = $this->view->render($this, 'record');

				echo $template;
			}
		}
    }

	public function results($params)
	{
		$go = false;

		global $global;

		$global['account'] = $this->model->read_account($params[0]);

		if (!empty($global['account']))
		{
			Session::set_value('vkye_time_zone', $global['account']['time_zone']);

			if (!empty($params[1]))
			{
				$global['custody_chain'] = $this->model->read_custody_chain($params[1]);

				if (!empty($global['custody_chain']) AND ($global['custody_chain']['type'] == 'covid_pcr' OR $global['custody_chain']['type'] == 'covid_an' OR $global['custody_chain']['type'] == 'covid_ac'))
				{
					Session::set_value('vkye_lang', $global['custody_chain']['lang']);

					$global['render'] = 'results';
					$go = true;
				}
			}
			else
			{
				$global['render'] = 'create';
				$go = true;
			}
		}

		if ($go == true)
		{
			if (Format::exist_ajax_request() == true)
	        {
	            if ($_POST['action'] == 'registry')
				{
					if (isset($_POST['accept_terms']))
					{
						$errors = [];

			            if (Validations::empty($_POST['firstname']) == false)
			                array_push($errors, ['firstname','{$lang.dont_leave_this_field_empty}']);

			            if (Validations::empty($_POST['lastname']) == false)
			                array_push($errors, ['lastname','{$lang.dont_leave_this_field_empty}']);

						if (Validations::empty($_POST['ife']) == false)
			                array_push($errors, ['ife','{$lang.dont_leave_this_field_empty}']);

			            if (Validations::empty($_POST['birth_date_year']) == false)
			                array_push($errors, ['birth_date_year','{$lang.dont_leave_this_field_empty}']);

			            if (Validations::empty($_POST['birth_date_month']) == false)
			                array_push($errors, ['birth_date_month','{$lang.dont_leave_this_field_empty}']);

			            if (Validations::empty($_POST['birth_date_day']) == false)
			                array_push($errors, ['birth_date_day','{$lang.dont_leave_this_field_empty}']);

			            if (Validations::empty($_POST['age']) == false)
			                array_push($errors, ['age','{$lang.dont_leave_this_field_empty}']);
			            else if (Validations::number('int', $_POST['age']) == false)
			                array_push($errors, ['age','{$lang.invalid_field}']);

			            if (Validations::empty($_POST['sex']) == false)
			                array_push($errors, ['sex','{$lang.dont_leave_this_field_empty}']);

						if ($global['account']['path'] != 'moonpalace')
						{
							if (Validations::empty($_POST['email']) == false)
							   array_push($errors, ['email','{$lang.dont_leave_this_field_empty}']);
						   else if (Validations::email($_POST['email']) == false)
							   array_push($errors, ['email','{$lang.invalid_field}']);

						   if (Validations::empty([$_POST['phone_country'],$_POST['phone_number']]) == false)
							   array_push($errors, ['phone_number','{$lang.dont_leave_this_field_empty}']);
						   else if (Validations::number('int', $_POST['phone_number']) == false)
							   array_push($errors, ['phone_number','{$lang.invalid_field}']);

						   if (Validations::empty($_POST['travel_to']) == false)
							   array_push($errors, ['travel_to','{$lang.dont_leave_this_field_empty}']);

						   if (Validations::empty($_POST['type']) == false)
							   array_push($errors, ['type','{$lang.dont_leave_this_field_empty}']);
						}

			            if (empty($errors))
			            {
			                $_POST['token'] = System::generate_random_string();
							$_POST['firstname'] = ucwords($_POST['firstname']);
							$_POST['lastname'] = ucwords($_POST['lastname']);
							$_POST['birth_date'] = $_POST['birth_date_year'] . '-' . $_POST['birth_date_month'] . '-' . $_POST['birth_date_day'];

							if ($global['account']['path'] != 'moonpalace')
							{
								$_POST['email'] = strtolower($_POST['email']);
								$_POST['travel_to'] = ucwords($_POST['travel_to']);
								$_POST['qr']['filename'] = 'covid_qr_' . $_POST['token'] . '_' . Dates::current_date('Y_m_d') . '_' . Dates::current_hour('H_i_s') . '.png';
							}
							else
							{
								$_POST['email'] = $global['account']['email'];
								$_POST['phone_country'] = $global['account']['phone']['country'];
								$_POST['phone_number'] = $global['account']['phone']['number'];
								$_POST['travel_to'] = 'No aplica';
								$_POST['type'] = 'covid_pcr';
							}

							$_POST['account'] = $global['account'];

			                $query = $this->model->create_custody_chain($_POST);

			                if (!empty($query))
			                {
			                    System::temporal('set_forced', 'covid', 'contact', $_POST);

								if ($global['account']['path'] != 'moonpalace')
								{
									$mail1 = new Mailer(true);

									try
									{
										$mail1->setFrom(Configuration::$vars['marbu']['email'], 'Marbu Salud');
										$mail1->addAddress($_POST['email'], $_POST['firstname'] . ' ' . $_POST['lastname']);
										$mail1->Subject = '¡' . Languages::email('hi')[Session::get_value('vkye_lang')] . ' ' . explode(' ',  $_POST['firstname'])[0] . '! ' . Languages::email('your_token_is')[Session::get_value('vkye_lang')] . ': ' . $_POST['token'];
										$mail1->Body =
										'<html>
											<head>
												<title>' . $mail1->Subject . '</title>
											</head>
											<body>
												<table style="width:100%;max-width:600px;margin:0px;padding:0px;border:0px;background-color:#004770;">
													<tr style="width:100%;margin:0px;padding:0px;border:0px;">
														<td style="width:100px;margin:0px;padding:20px 0px 20px 20px;border:0px;box-sizing:border-box;vertical-align:middle;">
															<img style="width:100px" src="https://' . Configuration::$domain . '/images/marbu_logotype_color_circle.png">
														</td>
														<td style="width:auto;margin:0px;padding:20px;border:0px;box-sizing:border-box;vertical-align:middle;">
															<table style="width:100%;margin:0px;padding:0px;border:0px;">
																<tr style="width:100%;margin:0px;padding:0px;border:0px;">
																	<td style="width:100%;margin:0px;padding:0px;border:0px;font-size:12px;font-weight:600;text-align:right;color:#fff;">Marbu Salud S.A. de C.V.</td>
																</tr>
																<tr style="width:100%;margin:0px;padding:0px;border:0px;">
																	<td style="width:100%;margin:0px;padding:0px;border:0px;font-size:12px;font-weight:400;text-align:right;color:#fff;">MSA1907259GA</td>
																</tr>
																<tr style="width:100%;margin:0px;padding:0px;border:0px;">
																	<td style="width:100%;margin:0px;padding:0px;border:0px;font-size:12px;font-weight:400;text-align:right;color:#fff;">Av. Del Sol SM47 M6 L21 Planta Alta</td>
																</tr>
																<tr style="width:100%;margin:0px;padding:0px;border:0px;">
																	<td style="width:100%;margin:0px;padding:0px;border:0px;font-size:12px;font-weight:400;text-align:right;color:#fff;">CP: 77506 Cancún, Qroo. México</td>
																</tr>
															</table>
														</td>
													</tr>
												</table>
												<table style="width:100%;max-width:600px;margin:20px 0px;padding:0px;border:1px dashed #000;box-sizing:border-box;background-color:#fff;">
													<tr style="width:100%;margin:0px;padding:0px;border:0px;">
														<td style="width:100%;margin:0px;padding:20px 20px 0px 20px;border:0px;box-sizing:border-box;font-size:18px;font-weight:600;text-align:center;text-transform:uppercase;color:#000;">' . Languages::email('your_token_is')[Session::get_value('vkye_lang')] . ': ' . $_POST['token'] . '</td>
													</tr>
													<tr style="width:100%;margin:0px;padding:0px;border:0px;">
														<td style="width:100%;margin:0px;padding:20px 20px 0px 20px;border:0px;box-sizing:border-box;font-size:12px;font-weight:400;text-align:center;color:#757575;">¡' . Languages::email('hi')[Session::get_value('vkye_lang')] . ' <strong>' . explode(' ', $_POST['firstname'])[0] . '</strong>! ' . Languages::email('your_results_next_email')[Session::get_value('vkye_lang')] . '</td>
													</tr>
													<tr style="width:100%;margin:0px;padding:0px;border:0px;">
														<td style="width:100%;margin:0px;padding:20px 20px 0px 20px;border:0px;box-sizing:border-box;">
															<a style="width:100%;display:block;margin:0px;padding:10px;border:1px solid #bdbdbd;border-radius:5px;box-sizing:border-box;background-color:#fff;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#757575;" href="https://api.whatsapp.com/send?phone=' . Configuration::$vars['marbu']['phone'] . '&text=Hola, soy ' . $_POST['firstname'] . ' ' . $_POST['lastname'] . '. Me gustaría agendar mi cita. Ya he registrado mis datos. Mi folio es: ' . $_POST['token'] . '">' . Languages::email('share_us_your_token')[Session::get_value('vkye_lang')] . '</a>
														</td>
													</tr>
													<tr style="width:100%;margin:0px;padding:0px;border:0px;">
														<td style="width:100%;margin:0px;padding:20px 20px 0px 20px;border:0px;box-sizing:border-box;">
															<img style="width:100%;" src="https://' . Configuration::$domain . '/uploads/' . $_POST['qr']['filename'] . '">
														</td>
													</tr>
													<tr style="width:100%;margin:0px;padding:0px;border:0px;">
														<td style="width:100%;margin:0px;padding:20px;border:0px;box-sizing:border-box;">
															<a style="width:100%;display:block;margin:0px;padding:10px;border:0px;border-radius:5px;box-sizing:border-box;background-color:#009688;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#fff;" href="https://' . Configuration::$domain . '/' . $global['account']['path'] . '/covid/' . $_POST['token'] . '">' . Languages::email('view_online_results')[Session::get_value('vkye_lang')] . '</a>
														</td>
													</tr>
												</table>
												<table style="width:100%;max-width:600px;margin:0px;padding:0px;border:0px;background-color:#0b5178;">
													<tr style="width:100%;margin:0px;padding:0px;border:0px;">
														<td style="width:100%;margin:0px;padding:20px 20px 0px 20px;border:0px;box-sizing:border-box;font-size:12px;font-weight:400;text-align:left;color:#fff;"><a style="text-decoration:none;color:#fff;" href="tel:' . Configuration::$vars['marbu']['phone'] . '">' . Configuration::$vars['marbu']['phone'] . '</a></td>
													</tr>
													<tr style="width:100%;margin:0px;padding:0px;border:0px;">
														<td style="width:100%;margin:0px;padding:0px 20px;border:0px;box-sizing:border-box;font-size:12px;font-weight:400;text-align:left;color:#fff;"><a style="text-decoration:none;color:#fff;" href="mailto:' . Configuration::$vars['marbu']['email'] . '">' . Configuration::$vars['marbu']['email'] . '</a></td>
													</tr>
													<tr style="width:100%;margin:0px;padding:0px;border:0px;">
														<td style="width:100%;margin:0px;padding:0px 20px 20px 20px;border:0px;box-sizing:border-box;font-size:12px;font-weight:400;text-align:left;color:#fff;"><a style="text-decoration:none;color:#fff;" href="https://' . Configuration::$vars['marbu']['website'] . '">' . Configuration::$vars['marbu']['website'] . '</a></td>
													</tr>
												</table>
												<table style="width:100%;max-width:600px;margin:0px;padding:0px;border:0px;background-color:#004770;">
													<tr style="width:100%;margin:0px;padding:0px;border:0px;">
														<td style="width:100%;margin:0px;padding:20px 20px 0px 20px;border:0px;box-sizing:border-box;font-size:12px;font-weight:400;text-align:left;color:#fff;">' . Languages::email('power_by')[Session::get_value('vkye_lang')] . ' <a style="font-weight:600;text-decoration:none;color:#fff;" href="https://id.one-consultores.com">' . Configuration::$web_page . ' ' . Configuration::$web_version . '</a></td>
													</tr
													<tr style="width:100%;margin:0px;padding:0px;border:0px;">
														<td style="width:100%;margin:0px;padding:0px 20px;border:0px;box-sizing:border-box;font-size:12px;font-weight:400;text-align:left;color:#fff;">Copyright (C) <a style="text-decoration:none;color:#fff;" href="https://one-consultores.com">One Consultores</a></td>
													</tr>
													<tr style="width:100%;margin:0px;padding:0px;border:0px;">
														<td style="width:100%;margin:0px;padding:0px 20px 20px 20px;border:0px;box-sizing:border-box;font-size:12px;font-weight:400;text-align:left;color:#fff;">Software ' . Languages::email('development_by')[Session::get_value('vkye_lang')] . ' <a style="text-decoration:none;color:#fff;" href="https://codemonkey.com.mx">Code Monkey</a></td>
													</tr>
												</table>
											</body>
										</html>';
										$mail1->send();
									}
									catch (Exception $e) {}

									$sms = new \Nexmo\Client\Credentials\Basic('51db0b68', 'd2TTUheuHp6BqYep');
									$sms = new \Nexmo\Client($sms);

									try
									{
										$sms->message()->send([
											'to' => $_POST['phone_country'] . $_POST['phone_number'],
											'from' => 'Marbu Salud',
											'text' => '¡' . Languages::email('hi')[Session::get_value('vkye_lang')] . ' ' . explode(' ',  $_POST['firstname'])[0] . '! ' . Languages::email('your_token_is')[Session::get_value('vkye_lang')] . ': ' . $_POST['token'] . '. ' . Languages::email('we_send_email_1')[Session::get_value('vkye_lang')] . ' ' . $_POST['email'] . ' ' . Languages::email('we_send_email_2')[Session::get_value('vkye_lang')] . ': https://' . Configuration::$domain . '/' . $global['account']['path'] . '/covid/' . $_POST['token'] . '. ' . Languages::email('power_by')[Session::get_value('vkye_lang')] . ' ' . Configuration::$web_page . ' ' . Configuration::$web_version . '.'
										]);
									}
									catch (Exception $e) {}
								}

								// $mail2 = new Mailer(true);
								//
								// try
								// {
								// 	$mail2->setFrom(Configuration::$vars['marbu']['email'], 'Marbu Salud');
								// 	$mail2->addAddress(Configuration::$vars['marbu']['email'], 'Marbu Salud');
								// 	$mail2->Subject = 'Prueba Covid. ' . $_POST['firstname'] . ' ' . $_POST['lastname'] . '. Folio: ' . $_POST['token'];
								// 	$mail2->Body =
								// 	'<html>
								// 		<head>
								// 			<title>' . $mail2->Subject . '</title>
								// 		</head>
								// 		<body>
								// 			<table style="width:100%;max-width:600px;margin:0px;padding:0px;border:0px;background-color:#fff;">
								// 				<tr style="width:100%;margin:0px;padding:0px;border:0px;">
								// 					<td style="width:50%;margin:0px;padding:0px;border:0px;font-size:14px;font-weight:600;text-align:left;color:#000;">Nombre:</td>
								// 					<td style="width:50%;margin:0px;padding:0px;border:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . $_POST['firstname'] . ' ' . $_POST['lastname'] . '</td>
								// 				</tr>
								// 				<tr style="width:100%;margin:0px;padding:0px;border:0px;">
								// 					<td style="width:50%;margin:0px;padding:0px;border:0px;font-size:14px;font-weight:600;text-align:left;color:#000;">Pasaporte:</td>
								// 					<td style="width:50%;margin:0px;padding:0px;border:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . $_POST['ife'] . '</td>
								// 				</tr>
								// 				<tr style="width:100%;margin:0px;padding:0px;border:0px;">
								// 					<td style="width:50%;margin:0px;padding:0px;border:0px;font-size:14px;font-weight:600;text-align:left;color:#000;">Nacimiento:</td>
								// 					<td style="width:50%;margin:0px;padding:0px;border:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . $_POST['birth_date'] . '</td>
								// 				</tr>
								// 				<tr style="width:100%;margin:0px;padding:0px;border:0px;">
								// 					<td style="width:50%;margin:0px;padding:0px;border:0px;font-size:14px;font-weight:600;text-align:left;color:#000;">Edad:</td>
								// 					<td style="width:50%;margin:0px;padding:0px;border:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . $_POST['age'] . ' Años</td>
								// 				</tr>
								// 				<tr style="width:100%;margin:0px;padding:0px;border:0px;">
								// 					<td style="width:50%;margin:0px;padding:0px;border:0px;font-size:14px;font-weight:600;text-align:left;color:#000;">Sexo:</td>
								// 					<td style="width:50%;margin:0px;padding:0px;border:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . $_POST['sex'] . '</td>
								// 				</tr>
								// 				<tr style="width:100%;margin:0px;padding:0px;border:0px;">
								// 					<td style="width:50%;margin:0px;padding:0px;border:0px;font-size:14px;font-weight:600;text-align:left;color:#000;">Email:</td>
								// 					<td style="width:50%;margin:0px;padding:0px;border:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . $_POST['email'] . '</td>
								// 				</tr>
								// 				<tr style="width:100%;margin:0px;padding:0px;border:0px;">
								// 					<td style="width:50%;margin:0px;padding:0px;border:0px;font-size:14px;font-weight:600;text-align:left;color:#000;">Teléfono:</td>
								// 					<td style="width:50%;margin:0px;padding:0px;border:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">+' . $_POST['phone_country'] . ' ' . $_POST['phone_number'] . '</td>
								// 				</tr>
								// 				<tr style="width:100%;margin:0px;padding:0px;border:0px;">
								// 					<td style="width:50%;margin:0px;padding:0px;border:0px;font-size:14px;font-weight:600;text-align:left;color:#000;">Viaja a:</td>
								// 					<td style="width:50%;margin:0px;padding:0px;border:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . $_POST['travel_to'] . '</td>
								// 				</tr>
								// 				<tr style="width:100%;margin:0px;padding:0px;border:0px;">
								// 					<td style="width:50%;margin:0px;padding:0px;border:0px;font-size:14px;font-weight:600;text-align:left;color:#000;">Tipo de prueba:</td>
								// 					<td style="width:50%;margin:0px;padding:0px;border:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . $_POST['type'] . '</td>
								// 				</tr>
								// 			</table>
								// 		</body>
								// 	</html>';
								// 	$mail2->send();
								// }
								// catch (Exception $e) {}

			                    echo json_encode([
			                        'status' => 'success',
			                        'message' => '{$lang.operation_success}'
			                    ]);
			                }
			                else
			                {
			                    echo json_encode([
			                        'status' => 'error',
			                        'message' => '{$lang.operation_error}'
			                    ]);
			                }
			            }
			            else
			            {
			                echo json_encode([
			                    'status' => 'error',
			                    'errors' => $errors
			                ]);
			            }
					}
					else
					{
						echo json_encode([
							'status' => 'error',
							'message' => '{$lang.accept_terms_error}'
						]);
					}
				}

				if ($_POST['action'] == 'restore_registry')
				{
					System::temporal('set_forced', 'covid', 'contact', []);

					echo json_encode([
						'status' => 'success',
						'message' => '{$lang.operation_success}'
					]);
				}
	        }
	        else
	        {
	            define('_title', 'Marbu Salud | {$lang.covid}');

	            if ($global['render'] == 'results' OR System::temporal('get_if_exists', 'covid', 'contact') == false)
					System::temporal('set_forced', 'covid', 'contact', []);

	            $template = $this->view->render($this, 'index');

	            echo $template;
	        }
		}
	}
}
