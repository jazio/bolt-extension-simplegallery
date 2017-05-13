<?php

namespace Bolt\Extension\jazio\SimpleGallery;

use Bolt\Extension\SimpleGalleryExtension;

/**
 * ExtensionName extension class.
 *
 * @author jazio <you@example.com>
 */
class SimpleGalleryExtension extends SimpleExtension
{
	public function getName()
  {
    return "SimpleGallery";
  }

  public function initialize()
  {
    $this->config = $this->getConfig();

    // If your extension has a 'config.yml', it is automatically loaded.
    if (!isset($this->config['gallery_path'])) { $this->config['gallery_path'] = "galleries/"; }
    if (!isset($this->config['pathstructure'])) { $this->config['pathstructure'] = "by_year"; }

    // Initialize the Twig function
    $this->addTwigFunction('GalleryList', 'twigGalleryList');
    $this->addTwigFunction('GalleryPreview', 'twigGalleryPreview');

  }

  public function twigGalleryList($slug="")
  {
    $images=$this->get_images($slug);
    $image_infos = $this->get_image_infos($images);

    return $image_infos;
  }

  public function twigGalleryPreview($slug="")
  {
    $images=$this->get_images($slug, $slugDate);
    return $images['online'].basename($images['images'][0]);
  }

  private function get_images($slug)
  {
    $contenttypes = $this->app['config']->get('contenttypes');
    $records = $this->app['storage']->getContent('galleries');

    foreach( $records as $record){
      if( $record['slug'] == $slug ){
        $record_found = $record;
        break;
      }
    }
    $date_conv = strtotime($record_found['datecreated']);
    if($this->config['pathstructure']== 'unsorted') {
      $folder = '/'.$slug.'/';
    }
    elseif($this->config['pathstructure']== 'by_year') {
      $folder = '/'.date('Y',$date_conv).'/'.date('F',$date_conv).'/'.$slug.'/';
    }
    else {
      $folder = '/';
      echo "path could not be set, please check pathstructure in settings!";
    }

    $path = $this->app['paths']['filespath'].'/'.$this->config['gallery_path'].$folder;
    $online_path = $this->config['gallery_path'].$folder;
    $images = glob($path . "*.{jpg,JPG,jpeg,JPEG}", GLOB_BRACE);
    $return_val = array(
                          "images" => $images,
                          "online" => $online_path,
                        );

    return $return_val;
  }
}
