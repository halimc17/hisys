<?
class Image {
    function init(){
        echo "Initialize";
    }
    function getImageInStr($html){
        $doc = new DOMDocument();
        @$doc->loadHTML($html);
        $tags = $doc->getElementsByTagName('img');

        foreach ($tags as $tag) {
            echo $tag->getAttribute('src');
        }

    }
    function upload_image($src,$path){

    }
    function resize_image($fileImage){
        
    }
    function getInfo_image($fileImage){
        
    }
}

?>