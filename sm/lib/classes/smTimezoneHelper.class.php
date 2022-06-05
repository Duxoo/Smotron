<?php
class smTimezoneHelper
{
	static public function allowedTimezones()
	{
		$system_timezones = waDateTime::getTimeZones();
		$timezones = array(
			'UTC' => '+00 Рейкьявик',
			'Europe/London' => '+01 Лондон, Лиссабон',
			'Europe/Kaliningrad' => '+02 Киев, Калининград',
			'Europe/Moscow' => '+03 Москва, Волгоград',
			'Europe/Samara' => '+04 Самара',
			'Asia/Yekaterinburg' => '+05 Екатеринбург, Ташкент',
			'Asia/Almaty' => '+06 Алматы, Бишкек',
			'Asia/Krasnoyarsk' => '+07 Красноярск, Новосибирск',
			'Asia/Makassar' => '+08 Шанхай, Сингапур',
			'Asia/Yakutsk' => '+09 Иркутск, Токио',
			'Australia/Melbourne' => '+10 Мельбурн, Сидней',
			'Asia/Sakhalin' => '+11 Сахалин, Владивосток',
			'Asia/Anadyr' => '+12 Анадырь, Камчатка, Магадан',
			'Pacific/Enderbury' => '+13 Эндербери, Тонгатапу',
			'Pacific/Kiritimati' => '+14 Киритимати',
			'Atlantic/Cape_Verde' => '-01 Кабе Верде',
			'Atlantic/South_Georgia' => '-02 Южная Георгия',
			'America/Araguaina' => '-03 Буэнос-Айрес, Сан-Паулу',
			'America/Toronto' => '-04 Нью-Йорк, Торонто',
			'America/Monterrey' => '-05 Чикаго, Монтеррей',
			'America/Denver' => '-06 Денвер, Ванкувер',
			'America/Phoenix' => '-07 Финикс',
			'Pacific/Pitcairn' => '-08 Питкэрн, Лос-Анджелес',
			'America/Adak' => '-09 Адак',
		);
		//print_r($system_timezones);
		foreach($timezones as $key => $timezone)
		{
			if(!isset($system_timezones[$key])) {unset($timezones[$key]);}
			/*
			else
			{
				$date_time = new DateTime('now');
				$tz = new DateTimeZone($key);
                $date_time->setTimezone($tz);
				$offset = $date_time->getOffset();
                $timezones[$key] = ($offset >= 0 ? '+' : '−').str_pad((float)abs($offset)/3600, 2, '0', STR_PAD_LEFT).' '.$timezone;
			}
			*/
		}
		return $timezones;
	}
	
	static public function timezone()
	{
		$timezones = self::allowedTimezones();
		
		// Попытаться получить часовой пояс по кукам
		$cookie_timezone = waRequest::cookie('timezone', null, 'string');
		if($cookie_timezone !== null)
		{
			
			$cookie_timezone = str_replace('%2F', '/', $cookie_timezone);
			if(!isset($timezones[$cookie_timezone])) {return waDateTime::getDefaultTimeZone();}
			if(wa()->getUser()->isAuth()) {$contact = new waContact(wa()->getUser()->getId()); $contact->set('timezone', $cookie_timezone); $contact->save();}
			return $cookie_timezone;
		}
	
		// Попытаться получить часовой пояс по контакту
		if(wa()->getUser()->isAuth())
		{
			$timezone = wa()->getUser()->get('timezone');
			if(!isset($timezones[$timezone]))
			{
				$timezone = waDateTime::getDefaultTimeZone();
				$contact = new waContact(wa()->getUser()->getId());
				$contact->set('timezone', $timezone); $contact->save();
				return $timezone;
			}
			wa()->getResponse()->setCookie('timezone', $timezone, time() + 12*30*86400, '/', '', false, true);
			return $timezone;
		}
		
		// Не удалось по кукам - пробуем автомат
		// TODO
		
		return 'Europe/Moscow';
	}
	
	public function setTimezone($timezone)
	{
		$timezones = smTimezoneHelper::allowedTimezones();
		if(!isset($timezones[$timezone])) {return;}
		
		if(wa()->getUser()->isAuth()) {$contact = new waContact(wa()->getUser()->getId()); $contact->set('timezone', $timezone); $contact->save();}
		wa()->getResponse()->setCookie('timezone', $timezone, time() + 12*30*86400, '/', '', false, true);
	}

	static public function convertTimeZone($datetime, $in_zone = null, $out_zone = null)
	{
		if($in_zone === null) {$in_zone = 'UTC';}
		if($out_zone === null) {$out_zone = self::timezone();}
		$datetime = new DateTime($datetime, new DateTimeZone($in_zone));
		//echo 'IN ZONE '.$in_zone.' '.$datetime->format('Y-m-d H:i:s P').'<br>';
		$datetime->setTimezone(new DateTimeZone($out_zone));
		//echo 'OUT ZONE '.$out_zone.' '.$datetime->format('Y-m-d H:i:s P').'<br>';
		return $datetime->format('Y-m-d H:i:s');
	}

	static public function utcTime()
	{
		return strtotime(gmdate('Y-m-d H:i:s'));
	}

	static public function userDateToUtcDatetimes($date)
	{
		return array(
			'start' => self::convertTimeZone($date.' 00:00:00', self::timezone(), 'UTC'),
			'end' => self::convertTimeZone($date.' 23:59:59', self::timezone(), 'UTC'),
		);
	}
}
