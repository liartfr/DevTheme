<?php
class Helper
{

	public static function cdn_that_image($image, $width)
    {
		if ( 0 === strpos( $image, '//' ) ) {
			$image = 'https:' . $image;
		}
		$image_url_parts = @parse_url( $image );

		if ( ! is_array( $image_url_parts ) || empty( $image_url_parts['host'] ) || empty( $image_url_parts['path'] ) ) return $image;
		if( strpos( $image_url_parts['host'], 'localhost') !== false || strpos( $image_url_parts['host'], '127.0.0.1') !== false) return $image;

		$image_host_path = $image_url_parts['host'] . $image_url_parts['path'];
		$subdomain = rand( 0, 2 );
		if($subdomain==0){
		   $new_url  = "https://i.meln.top/";
		}else{
		   $new_url  = "https://i.meln.top/".$subdomain."/";
		}
		$new_url .= 'width/' . $width . '/n/'; //resize image, keep proportions
		
		$first_img = $new_url . $image_host_path;

        return $first_img;
    }

    public function previousKey()
    {
        global $page;
		global $pages;
		// if (method_exists($page, 'previousKey')) return $page->previousKey(); //from Bludit 3 this is core function
        $currentKey = $page->key();
        $keys = $pages->getPublishedDB(true);
        $position = array_search($currentKey, $keys) + 1;
        if (isset($keys[$position])) {
            return $keys[$position];
        }
        return false;
    }

    public function nextKey()
    {
        global $page;
		global $pages;
        //if (method_exists($page, 'nextKey')) return $page->nextKey(); //from Bludit 3 this is core function
        $currentKey = $page->key();
        $keys = $pages->getPublishedDB(true);
        $position = array_search($currentKey, $keys) - 1;
        if (isset($keys[$position])) {
            return $keys[$position];
        }
        return false;
    }

    public function head_description()
    {
        global $site;
        global $WHERE_AM_I;
        global $page;
        global $url;

        $description = $site->description();

        if ($WHERE_AM_I == 'page') {
            $description = $page->description();
            if (empty($description)) {
                $content = str_replace('<', ' <', $page->content(false));
                $content = html_entity_decode($content);
                $description = Text::truncate(Text::removeHTMLTags($content), 250);
                $description = trim(preg_replace('/\s+/', ' ', $description));//remove repeated spaces
            }
        } elseif ($WHERE_AM_I == 'category') {
            try {
                $categoryKey = $url->slug();
                $category = new Category($categoryKey);
                $description = $category->description();
            }
			catch (Exception $e) {
				// description from the site
            }
        }
        return '<meta name="description" content="' . $description . '">' . PHP_EOL;
    }

	public function description()
    {
        global $site;
        global $WHERE_AM_I;
        global $url;

        $description = $site->description();

        if ($WHERE_AM_I == 'category') {
            try {
                $categoryKey = $url->slug();
                $category = new Category($categoryKey);
                $description = $category->description();
            }
			catch (Exception $e) {
				// description from the site
            }
        }
        return $description;
    }
	public function getPageDescription($page)
    {
		$description = $page->description();
		if (empty($description)) {
			$content = str_replace('<', ' <', $page->content(false));
			$content = html_entity_decode($content);
			$description = Text::truncate(Text::removeHTMLTags($content), 150);
			$description = trim(preg_replace('/\s+/', ' ', $description));//remove repeated spaces
		}
		return $description;
	}

	/**
	 * Highlight phrase in text and return snippet
	 * @param string $text
	 * @param string $phrase
	 * @param int $radius
	 * @param string $ending
	 * @return string
	 */
	public function snippet($text, $phrase, $radius = 100, $ending = '...') {

		$phrase =trim(preg_replace('/\s+/', ' ',$phrase));
		$words = join('|', explode(' ', preg_quote($phrase)));

		$phraseLen = strlen($phrase);
		if ($radius < $phraseLen) {
			$radius = $phraseLen;
		}

		$phrases = explode (' ',$phrase);
		$pos = -1;
		foreach ($phrases as $phrase) {
			$pos = strpos(strtolower($text), strtolower($phrase));
			if ($pos > -1) break;
		}

		$startPos = 0;
		if ($pos > $radius) {
			$startPos = $pos - $radius;
		}

		$textLen = strlen($text);

		$endPos = $pos + $phraseLen + $radius;
		if ($endPos >= $textLen) {
			$endPos = $textLen;
		}

		$excerpt = substr($text, $startPos, $endPos - $startPos);
		if ($startPos != 0) {
			$excerpt = substr_replace($excerpt, $ending, 0, $phraseLen);
		}

		if ($endPos != $textLen) {
			$excerpt = substr_replace($excerpt, $ending, -$phraseLen);
		}

		$excerpt= preg_replace('#'.$words.'#iu', "<strong>\$0</strong>", $excerpt);
		return $excerpt;
	}

	/**
	 * Limit text to number of words
	 * @param string $text The input string
	 * @param int $limit Number of words to return
	 * @param string $ending Text to add at end
	 * @return string
	 */
	function limit_text_words($text, $limit, $ending = '...') {
		if (str_word_count($text, 0) > $limit) {
			$words = str_word_count($text, 2);
			$pos = array_keys($words);
			$text = substr($text, 0, $pos[$limit]) . $ending;
		}
		return $text;
    }

	public function slogan()
    {
        global $site;
        global $WHERE_AM_I;
        global $url;
        $slogan = $site->slogan();
        if ($WHERE_AM_I == 'category') {
            try {
                $categoryKey = $url->slug();
                $category = new Category($categoryKey);
                $slogan = $category->name();
            }
			catch (Exception $e) {
				// slogan from the site
            }
        }
        return $slogan;
    }

    public static function content2excerpt($cont,  $limit=500 , $ending='...' )
    {
        $cont = str_replace('<', ' <', $cont);
        $cont = html_entity_decode($cont);
        $description = Text::truncate(Text::removeHTMLTags($cont), $limit);
        $description = trim(preg_replace('/\s+/', ' ', $description));//remove repeated spaces
        return $description. $ending;
    }

    public static function getRelated($max = 3, $similar = true)
    {
        global $WHERE_AM_I;
        global $page;
        if ($WHERE_AM_I == 'page') {
            $currentKey = $page->key();
            if (!$page->category()) return '';
            $currentCategory = getCategory($page->categoryKey());
            if (count($currentCategory->pages()) >= $max + 1) {
                $allCatPages = $currentCategory->pages();
				//remove curent page
                $allCatPages = array_diff($allCatPages, array($currentKey));

				//sort rest pages by similarity O(N**3)
                if ($similar) {
                    usort($allCatPages, function ($a, $b) use ($currentKey) {
                        similar_text($currentKey, $a, $percentA);
                        similar_text($currentKey, $b, $percentB);
                        return $percentA === $percentB ? 0 : ($percentA > $percentB ? -1 : 1);
                    });
                }
				//or just randomize
                else {
                    shuffle($allCatPages);
                }
                $related = array();
                for ($i = 0; $i < $max; $i++) {
                    $item = new Page($allCatPages[$i]);
                    if ($item->published()) {
                        $related[] = $item;
                    }
                }
                return $related;
            }

        }
        return '';
    }
}

