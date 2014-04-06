<?php

class RegistrationsTableSeeder extends Seeder {

	public function run()
	{
		Registration::truncate();

		Registration::insert(array(
			array('prefix' => 'YA ','country_id' => 'af',),
			array('prefix' => 'ZA ','country_id' => 'al',),
			array('prefix' => '7T ','country_id' => 'dz',),
			array('prefix' => 'C3 ','country_id' => 'ad',),
			array('prefix' => 'D2 ','country_id' => 'ao',),
			array('prefix' => 'V2 ','country_id' => 'ag',),
			array('prefix' => 'LQ ','country_id' => 'ar',),
			array('prefix' => 'LV ','country_id' => 'ar',),
			array('prefix' => 'EK ','country_id' => 'am',),
			array('prefix' => 'P4 ','country_id' => 'aw',),
			array('prefix' => 'VH ','country_id' => 'au',),
			array('prefix' => '4K ','country_id' => 'az',),
			array('prefix' => 'C6 ','country_id' => 'bs',),
			array('prefix' => 'A9C ','country_id' => 'bh',),
			array('prefix' => 'S2 ','country_id' => 'bd',),
			array('prefix' => '8P ','country_id' => 'bb',),
			array('prefix' => 'EW ','country_id' => 'by',),
			array('prefix' => 'OO ','country_id' => 'be',),
			array('prefix' => 'V3 ','country_id' => 'bz',),
			array('prefix' => 'TY ','country_id' => 'bj',),
			array('prefix' => 'VP','country_id' => 'bm',),
			array('prefix' => 'A5 ','country_id' => 'bt',),
			array('prefix' => 'CP ','country_id' => 'bo',),
			array('prefix' => 'T9 ','country_id' => 'ba',),
			array('prefix' => 'A2 ','country_id' => 'bw',),
			array('prefix' => 'PP ','country_id' => 'br',),
			array('prefix' => 'PR ','country_id' => 'br',),
			array('prefix' => 'PT ','country_id' => 'br',),
			array('prefix' => 'PU ','country_id' => 'br',),
			array('prefix' => 'V8 ','country_id' => 'bn',),
			array('prefix' => 'LZ ','country_id' => 'bg',),
			array('prefix' => 'XT ','country_id' => 'bf',),
			array('prefix' => '9U ','country_id' => 'bi',),
			array('prefix' => 'XU ','country_id' => 'kh',),
			array('prefix' => 'TJ ','country_id' => 'cm',),
			array('prefix' => 'C ','country_id' => 'ca',),
			array('prefix' => 'EC ','country_id' => 'es',),
			array('prefix' => 'D4 ','country_id' => 'cv',),
			array('prefix' => 'TL ','country_id' => 'cf',),
			array('prefix' => 'TT ','country_id' => 'td',),
			array('prefix' => 'CC ','country_id' => 'cl',),
			array('prefix' => 'B ','country_id' => 'cn',),
			array('prefix' => 'HK ','country_id' => 'co',),
			array('prefix' => 'D6 ','country_id' => 'km',),
			array('prefix' => 'TN ','country_id' => 'cg',),
			array('prefix' => 'E5 ','country_id' => 'ck',),
			array('prefix' => '9Q ','country_id' => 'cd',),
			array('prefix' => 'TI ','country_id' => 'cr',),
			array('prefix' => '9A ','country_id' => 'hr',),
			array('prefix' => 'CU','country_id' => 'cu',),
			array('prefix' => '5B ','country_id' => 'cy',),
			array('prefix' => 'OK ','country_id' => 'cz',),
			array('prefix' => 'OY ','country_id' => 'dk',),
			array('prefix' => 'J2 ','country_id' => 'dj',),
			array('prefix' => 'J7 ','country_id' => 'dm',),
			array('prefix' => 'HI ','country_id' => 'do',),
			array('prefix' => 'HC ','country_id' => 'ec',),
			array('prefix' => 'SU ','country_id' => 'eg',),
			array('prefix' => 'YS ','country_id' => 'sv',),
			array('prefix' => '3C ','country_id' => 'gq',),
			array('prefix' => 'E3 ','country_id' => 'er',),
			array('prefix' => 'ES ','country_id' => 'ee',),
			array('prefix' => 'ET ','country_id' => 'et',),
			array('prefix' => 'DQ ','country_id' => 'fj',),
			array('prefix' => 'OH ','country_id' => 'fi',),
			array('prefix' => 'F ','country_id' => 'fr',),
			array('prefix' => 'TR ','country_id' => 'ga',),
			array('prefix' => 'C5 ','country_id' => 'gm',),
			array('prefix' => '4L ','country_id' => 'ge',),
			array('prefix' => 'D ','country_id' => 'de',),
			array('prefix' => '9G ','country_id' => 'gh',),
			array('prefix' => 'SX ','country_id' => 'gr',),
			array('prefix' => 'J3 ','country_id' => 'gd',),
			array('prefix' => 'TG ','country_id' => 'gt',),
			array('prefix' => '3X ','country_id' => 'gn',),
			array('prefix' => 'J5 ','country_id' => 'gw',),
			array('prefix' => '8R ','country_id' => 'gy',),
			array('prefix' => 'HH ','country_id' => 'ht',),
			array('prefix' => 'HR ','country_id' => 'hn',),
			array('prefix' => 'HA ','country_id' => 'hu',),
			array('prefix' => 'TF ','country_id' => 'is',),
			array('prefix' => 'VT ','country_id' => 'in',),
			array('prefix' => 'PK ','country_id' => 'id',),
			array('prefix' => 'EP ','country_id' => 'ir',),
			array('prefix' => 'YI ','country_id' => 'iq',),
			array('prefix' => 'EI ','country_id' => 'ie',),
			array('prefix' => 'M ','country_id' => 'im',),
			array('prefix' => '4X ','country_id' => 'il',),
			array('prefix' => 'I ','country_id' => 'it',),
			array('prefix' => 'TU ','country_id' => 'ci',),
			array('prefix' => '6Y ','country_id' => 'jm',),
			array('prefix' => 'JA ','country_id' => 'jp',),
			array('prefix' => 'JY ','country_id' => 'jo',),
			array('prefix' => 'UN ','country_id' => 'kz',),
			array('prefix' => '5Y ','country_id' => 'ke',),
			array('prefix' => 'T3 ','country_id' => 'ki',),
			array('prefix' => 'P ','country_id' => 'kp',),
			array('prefix' => 'HL ','country_id' => 'kr',),
			array('prefix' => '9K ','country_id' => 'kw',),
			array('prefix' => 'EX ','country_id' => 'kg',),
			array('prefix' => 'RDPL ','country_id' => 'la',),
			array('prefix' => 'YL ','country_id' => 'lv',),
			array('prefix' => 'OD ','country_id' => 'lb',),
			array('prefix' => '7P ','country_id' => 'ls',),
			array('prefix' => 'A8 ','country_id' => 'lr',),
			array('prefix' => '5A ','country_id' => 'ly',),
			array('prefix' => 'LY ','country_id' => 'lt',),
			array('prefix' => 'LX ','country_id' => 'lu',),
			array('prefix' => 'Z3 ','country_id' => 'mk',),
			array('prefix' => '5R ','country_id' => 'mg',),
			array('prefix' => '7Q ','country_id' => 'mw',),
			array('prefix' => '9M ','country_id' => 'my',),
			array('prefix' => '8Q ','country_id' => 'mv',),
			array('prefix' => 'TZ ','country_id' => 'ml',),
			array('prefix' => '9H ','country_id' => 'mt',),
			array('prefix' => 'V7 ','country_id' => 'mh',),
			array('prefix' => '5T ','country_id' => 'mr',),
			array('prefix' => '3B ','country_id' => 'mu',),
			array('prefix' => 'XA ','country_id' => 'mx',),
			array('prefix' => 'XB ','country_id' => 'mx',),
			array('prefix' => 'XC ','country_id' => 'mx',),
			array('prefix' => 'V6 ','country_id' => 'fm',),
			array('prefix' => 'ER ','country_id' => 'md',),
			array('prefix' => '3A ','country_id' => 'mc',),
			array('prefix' => 'JU ','country_id' => 'mn',),
			array('prefix' => '4O ','country_id' => 'me',),
			array('prefix' => 'CN ','country_id' => 'ma',),
			array('prefix' => 'C9 ','country_id' => 'mz',),
			array('prefix' => 'XY ','country_id' => 'mm',),
			array('prefix' => 'XZ ','country_id' => 'mm',),
			array('prefix' => 'V5 ','country_id' => 'na',),
			array('prefix' => 'C2 ','country_id' => 'nr',),
			array('prefix' => '9N ','country_id' => 'np',),
			array('prefix' => 'PH ','country_id' => 'nl',),
			array('prefix' => 'PJ ','country_id' => 'an',),
			array('prefix' => 'ZK ','country_id' => 'nz',),
			array('prefix' => 'YN ','country_id' => 'ni',),
			array('prefix' => '5U ','country_id' => 'ne',),
			array('prefix' => '5N ','country_id' => 'ng',),
			array('prefix' => 'LN ','country_id' => 'no',),
			array('prefix' => 'A4O ','country_id' => 'om',),
			array('prefix' => 'AP ','country_id' => 'pk',),
			array('prefix' => 'HP ','country_id' => 'pa',),
			array('prefix' => 'P2 ','country_id' => 'pg',),
			array('prefix' => 'ZP ','country_id' => 'py',),
			array('prefix' => 'OB ','country_id' => 'pe',),
			array('prefix' => 'RPC ','country_id' => 'ph',),
			array('prefix' => 'SP ','country_id' => 'pl',),
			array('prefix' => 'CS ','country_id' => 'pt',),
			array('prefix' => 'A7 ','country_id' => 'qa',),
			array('prefix' => 'YR ','country_id' => 'ro',),
			array('prefix' => 'RA ','country_id' => 'ru',),
			array('prefix' => '9XR ','country_id' => 'rw',),
			array('prefix' => 'V4 ','country_id' => 'kn',),
			array('prefix' => 'J6 ','country_id' => 'lc',),
			array('prefix' => 'J8 ','country_id' => 'vc',),
			array('prefix' => '5W ','country_id' => 'ws',),
			array('prefix' => 'T7 ','country_id' => 'sm',),
			array('prefix' => 'S9 ','country_id' => 'st',),
			array('prefix' => 'HZ ','country_id' => 'sa',),
			array('prefix' => '6V ','country_id' => 'sn',),
			array('prefix' => '6W ','country_id' => 'sn',),
			array('prefix' => 'YU ','country_id' => 'rs',),
			array('prefix' => 'S7 ','country_id' => 'sc',),
			array('prefix' => '9L ','country_id' => 'sl',),
			array('prefix' => '9V ','country_id' => 'sg',),
			array('prefix' => 'OM ','country_id' => 'sk',),
			array('prefix' => 'S5 ','country_id' => 'si',),
			array('prefix' => 'H4 ','country_id' => 'sb',),
			array('prefix' => '6O ','country_id' => 'so',),
			array('prefix' => 'ZS ','country_id' => 'za',),
			array('prefix' => 'ZT ','country_id' => 'za',),
			array('prefix' => 'ZU ','country_id' => 'za',),
			array('prefix' => '4R ','country_id' => 'lk',),
			array('prefix' => 'ST ','country_id' => 'sd',),
			array('prefix' => 'PZ ','country_id' => 'sr',),
			array('prefix' => '3D ','country_id' => 'sz',),
			array('prefix' => 'SE ','country_id' => 'se',),
			array('prefix' => 'HB ','country_id' => 'ch',),
			array('prefix' => 'YK ','country_id' => 'sy',),
			array('prefix' => 'EY ','country_id' => 'tj',),
			array('prefix' => '5H ','country_id' => 'tz',),
			array('prefix' => 'HS ','country_id' => 'th',),
			array('prefix' => '5V ','country_id' => 'tg',),
			array('prefix' => 'A3 ','country_id' => 'to',),
			array('prefix' => '9Y ','country_id' => 'tt',),
			array('prefix' => 'TS ','country_id' => 'tn',),
			array('prefix' => 'TC ','country_id' => 'tr',),
			array('prefix' => 'EZ ','country_id' => 'tm',),
			array('prefix' => 'T2 ','country_id' => 'tv',),
			array('prefix' => '5X ','country_id' => 'ug',),
			array('prefix' => 'UR ','country_id' => 'ua',),
			array('prefix' => 'A6 ','country_id' => 'ae',),
			array('prefix' => 'G ','country_id' => 'gb',),
			array('prefix' => 'N ','country_id' => 'us',),
			array('prefix' => 'CX ','country_id' => 'uy',),
			array('prefix' => 'UK ','country_id' => 'uz',),
			array('prefix' => 'YJ ','country_id' => 'vu',),
			array('prefix' => 'YV ','country_id' => 've',),
			array('prefix' => 'VN ','country_id' => '',),
			array('prefix' => '7O ','country_id' => 'ye',),
			array('prefix' => '9J ','country_id' => 'zm',),
			array('prefix' => 'Z ','country_id' => 'zw',),
			array('prefix' => 'OE','country_id' => 'at',),
			array('prefix' => '4U','country_id' => 'un',),
		));
	}

}