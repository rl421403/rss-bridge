<?php
class RTBFBridge extends BridgeAbstract {
	public function loadMetadatas() {
		$this->name = "RTBF Bridge";
		$this->uri = "http://www.rtbf.be/auvio/emissions";
		$this->description = "Returns the newest RTBF videos by series ID";
		$this->maintainer = "Frenzie";

		$this->parameters[] =
		'[
			{
				"type" : "text",
				"identifier" : "c",
				"name" : "series id",
				"exampleValue" : "9500",
				"required" : "required"
			}
		]';
	}

	public function collectData(array $param) {
		$html = '';
		$limit = 10;
		$count = 0;

		if (isset($param['c'])) {
			$html = file_get_html('http://www.rtbf.be/auvio/emissions/detail?id='.$param['c']) or $this->returnError('Could not request RTBF.', 404);

			foreach($html->find('.rtbf-media-grid article') as $element) {
				if($count < $limit) {
					$item = new \Item();
					$item->id = $element->getAttribute('data-id');
					$item->uri = 'http://www.rtbf.be/auvio/detail?id='.$item->id;
					$thumbnailUriSrcSet = explode(',', $element->find('figure .www-img-16by9 img', 0)->getAttribute('data-srcset'));
					$thumbnailUriLastSrc = end($thumbnailUriSrcSet);
					$item->thumbnailUri = explode(' ', $thumbnailUriLastSrc)[0];
					$item->title = trim($element->find('h3',0)->plaintext) . ' - ' . trim($element->find('h4',0)->plaintext);
					$item->timestamp = strtotime($element->find('time', 0)->getAttribute('datetime'));
					$item->content = '<a href="' . $item->uri . '"><img src="' . $item->thumbnailUri . '" /></a>';
					$this->items[] = $item;
					$count++;
				}
			}
		}
		else {
			$this->returnError('You must specify a series id.', 400);
		}
	}

	public function getName(){
		return (!empty($this->request) ? $this->request .' - ' : '') .'RTBF Bridge';
	}

	public function getURI(){
		return 'http://www.rtbf.be/auvio/emissions';
	}

	public function getCacheDuration(){
		return 21600; // 6 hours
	}
}
