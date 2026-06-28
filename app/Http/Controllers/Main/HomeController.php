<?php
	/**
	 * Copyright (C) ZubDev Digital Media - All Rights Reserved
	 *
	 * File: homeController.php
	 * Author: Zubayr Ganiyu
	 *   Email: <seunexseun@gmail.com>
	 *   Website: https://zubdev.net
	 * Date: 6/28/26
	 * Time: 7:37 AM
	 */


	namespace App\Http\Controllers\Main;

	use App\Http\Controllers\Controller;
    use Inertia\Inertia;

    class HomeController extends Controller {
		public function index()
		{
			return Inertia::render('Home');
		}
	}
