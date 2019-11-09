<?php
function filter($imagePath) {
    $imagick = new \Imagick(realpath($imagePath));
    $matrix = [
        [0, -1, 0],
        [-1, 5, -1],
        [0, -1, 0],
    ];
    
    $kernel = \ImagickKernel::fromMatrix($matrix);
    $strength = 0.5;    
    $kernel->scale($strength, \Imagick::NORMALIZE_KERNEL_VALUE);    
    $kernel->addUnityKernel(1 - $strength);

    $imagick->filter($kernel);
    header("Content-Type: image/jpg");
    $imagick->writeImage ("output.jpg");
    echo "DONE\n";
}

function flipImage($imagePath) {
    $imagick = new \Imagick(realpath($imagePath));
    $imagick->flipImage();
    header("Content-Type: image/jpg");
    $imagick->writeImage ("output.jpg");
    echo "DONE\n"; 
}

function waterMarking($imagePath){
    $Imagick = new Imagick();
    /* Create a drawing object and set the font size */
    $ImagickDraw = new ImagickDraw();
    $ImagickDraw->setFontSize( 50 );
    /* Read image into object*/
    $Imagick->readImage( $imagePath );
    /* Seek the place for the text */
    $ImagickDraw->setGravity( Imagick::GRAVITY_CENTER );
    /* Write the text on the image */
    $Imagick->annotateImage( $ImagickDraw, 4, 20, 0, "Test Watermark" );
    /* Set format to png */
    $Imagick->setImageFormat( 'png' );
    /* Output */
    header( "Content-Type: image/{$Imagick->getImageFormat()}" );
    $Imagick->writeImage ("output.jpg");
    echo "DONE\n";
}
function mergeImages($im1,$im2){
    $layerMethodType = imagick::LAYERMETHOD_COMPARECLEAR;
    $img1 = new \Imagick(realpath($im1));

    $img2 = new \Imagick(realpath($im2));
    $img1->addImage($img2);
    $img1->setImageFormat('png');

    $result = $img1->mergeImageLayers($layerMethodType);
    header("Content-Type: image/png");

    $result->writeImage ("output.jpg");
    echo "DONE\n";
}

function getColorStatistics($histogramElements, $colorChannel){
    $colorStatistics = [];
 
    foreach ($histogramElements as $histogramElement) {
        $color = $histogramElement->getColorValue($colorChannel);
        $color = intval($color * 255);
        $count = $histogramElement->getColorCount();
 
        if (array_key_exists($color, $colorStatistics)) {
            $colorStatistics[$color] += $count;
        }
        else {
            $colorStatistics[$color] = $count;
        }
    }
 
    ksort($colorStatistics);
     
    return $colorStatistics;
}
 
function getImageHistogram($imagePath){
    $backgroundColor = 'black';
 
    $draw = new \ImagickDraw();
    $draw->setStrokeWidth(0); //make the lines be as thin as possible
 
    $imagick = new \Imagick();
    $imagick->newImage(500, 500, $backgroundColor);
    $imagick->setImageFormat("png");
    $imagick->drawImage($draw);
 
    $histogramWidth = 256;
    $histogramHeight = 100; // the height for each RGB segment
 
    $imagick = new \Imagick(realpath($imagePath));
    //Resize the image to be small, otherwise PHP tends to run out of memory
    //This might lead to bad results for images that are pathologically 'pixelly'
    $imagick->adaptiveResizeImage(200, 200, true);
    $histogramElements = $imagick->getImageHistogram();
 
    $histogram = new \Imagick();
    $histogram->newpseudoimage($histogramWidth, $histogramHeight * 3, 'xc:black');
    $histogram->setImageFormat('png');
 
    $getMax = function ($carry, $item) {
        if ($item > $carry) {
            return $item;
        }
        return $carry;
    };
 
    $colorValues = [
        'red' => getColorStatistics($histogramElements, \Imagick::COLOR_RED),
        'lime' => getColorStatistics($histogramElements, \Imagick::COLOR_GREEN),
        'blue' => getColorStatistics($histogramElements, \Imagick::COLOR_BLUE),
    ];
 
    $max = array_reduce($colorValues['red'], $getMax, 0);
    $max = array_reduce($colorValues['lime'], $getMax, $max);
    $max = array_reduce($colorValues['blue'], $getMax, $max);
 
    $scale =  $histogramHeight / $max;
 
    $count = 0;
    foreach ($colorValues as $color => $values) {
        $draw->setstrokecolor($color);
 
        $offset = ($count + 1) * $histogramHeight;
 
        foreach ($values as $index => $value) {
            $draw->line($index, $offset, $index, $offset - ($value * $scale));
        }
        $count++;
    }
 
    $histogram->drawImage($draw);
 
    header("Content-Type: image/png");
    $histogram->writeImage ("output.jpg");
    echo "DONE\n";
}

filter("car.jpg");
// flipImage("bg.jpg");
// waterMarking("bg.jpg");
// getImageHistogram("car.jpg");
// mergeImages("bg.jpg","car.jpg");

?>