<?php

/*
Script to populate the support message table
*/
class Controller_tests_supportmessageacript extends Crunchbutton_Controller_Account {

	public function init() {

		$supports = Support::q( 'SELECT * FROM support ORDER BY id_support DESC' );

		foreach ( $supports as $support ) {
			
			// 1: Update the support table with the id_admin
			if( !$support->id_admin ){
				$admin = Admin::q( 'SELECT a.id_admin FROM support_answer s INNER JOIN admin a ON a.name = s.name WHERE s.id_support = ' . $support->id_support . ' ORDER BY s.id_support_answer ASC LIMIT 1' );
				if( $admin->id_admin ){
					$support->id_admin = $admin->id_admin;
					$support->save();
				}	
			}
		
			// 2: Create the first message
			if( $support->phone && $support->phone ){
				// Check if the support message was already exported
				$messages = Support_Message::q( 'SELECT * FROM support_message WHERE id_support = ' . $support->id_support . ' AND name = "' . $support->name . '" AND phone = "' . $support->phone . '" AND date = "' . $support->date()->format( 'Y-m-d H:i:s' ) . '"' );
				if( $messages->count() == 0 ){
					$message = new Support_Message();
					$message->id_support = $support->id_support;
					$message->type = 'sms';
					$message->from = 'client';
					$message->visibility = 'external';
					$message->phone = $support->phone;
					$message->body = $support->message;
					$message->name = $support->name;
					$message->date = $support->date()->format( 'Y-m-d H:i:s' );
					$message->id_admin = $admin->id_admin;
					$message->from = 'client';
					$message->save();	
				}
			}

			// 3: Copy the data from support_answer to support_message
			$support_answers = Support_Answer::q( 'SELECT * FROM support_answer WHERE id_support = ' . $support->id_support );
			
			foreach( $support_answers as $sa ){
				
				// Check if the support message was already exported
				$messages = Support_Message::q( 'SELECT * FROM support_message WHERE id_support = ' . $support->id_support . ' AND name = "' . $sa->name . '" AND phone = "' . $sa->phone . '" AND date = "' . $sa->date()->format( 'Y-m-d H:i:s' ) . '"' );

				if( $messages->count() == 0 ){
					$message = new Support_Message();
					$message->id_support = $sa->id_support;
					$message->type = 'sms';
					$message->from = 'client';
					$message->visibility = 'external';
					$message->phone = $sa->phone;
					$message->body = $sa->message;
					$message->name = $sa->name;
					$message->date = $sa->date()->format( 'Y-m-d H:i:s' );
					// check if it is a message from an admin (rep)
					$admin = Admin::q( 'SELECT * FROM admin WHERE name = "' . $sa->name . '"' );
					if( $admin->id_admin ){
						$message->id_admin = $admin->id_admin;
						$message->from = 'rep';
					}
					$message->save();	
				}
			}

			// 4: Copy the data from support_note to support_message
			$support_notes = Support_Note::q( 'SELECT s.* FROM support_note s INNER JOIN support_answer a ON a.id_support = s.id_support AND s.text != a.message WHERE s.id_support = ' . $support->id_support );
			
			foreach( $support_notes as $sn ){
				
				// Check if the support message was already exported
				$body = $sn->text;
				if ( strpos( $body, '"' ) !== false ) {
					$body = explode( '"' , $body );
					$body = $body[ 0 ];
				} 
				if ( strpos( $body, "'" ) !== false ) {
					$body = explode( "'" , $body );
					$body = $body[ 0 ];
				} 

				$query = 'SELECT * FROM support_message WHERE id_support = ' . $support->id_support . ' AND body like "%' . $body . '%"';
				$messages = Support_Message::q( $query );

				if( $messages->count() == 0 ){
				
					$message = new Support_Message();
					$message->id_support = $sn->id_support;

					$message->from = 'client';

					switch ( $sn->from ) {
						case 'rep':
							$message->id_admin = $support->id_admin;
							$admin = Admin::o( $support->id_admin );
							$message->phone = $admin->phone;
							$message->name = $admin->name;
							$message->from = 'rep';
							if( $sn->visibility == 'internal' ){
								$message->type = 'note';
							} else {
								$message->type = 'sms';
							}
							break;
						case 'client':
							$message->phone = $support->phone;
							$message->name = $support->name;
							$message->type = 'sms';
							$message->from = 'client';
							break;
						case 'system':
							$message->type = 'note';
							$message->from = 'system';
							break;
					}

					if( !$message->type ){
						$message->type = 'system';
					}

					if( $message->phone == $message->name ){
						$message->name == Null;
					}

					$message->visibility = $sn->visibility;
					$message->body = $sn->text;
					
					// $date = 

					$message->date = $sn->date()->format( 'Y-m-d H:i:s' );
					
					$message->save();	
				}
			}
		}
		echo 'ok';
	}
}
