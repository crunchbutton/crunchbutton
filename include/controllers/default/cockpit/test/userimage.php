<?php
class Controller_test_userimage extends Crunchbutton_Controller_Account {
	public function init() {
		$users = User::q('
			select `user`.* from `user` left join user_auth using(id_user)
			where
				user_auth.active=true
				and user.active=true
				and ((user_auth.type = "local" and user_auth.email LIKE "%@%") or (user_auth.type = "facebook"))
				group by `user`.id_user
				order by `user`.id_user
		');
		$c = 0;
		foreach ($users as $user) {
			if ($user->image()) {
				$image = ['name' => $user->name, 'image' => $user->image()];
				if (strpos($user->image(),'gravitar')) {
					$image['type'] = 'gravitar';
				} else {
					$image['type'] = 'facebook';
				}
				$images[] = $image;
				$c++;
			}
		}
		
		Cana::view()->images = $images;
		Cana::view()->layout('layout/blank');
		Cana::view()->useFilter(false);
		Cana::view()->display('test/userimage');
	}
}