<?php

namespace AppBundle\Twig;
class DateExtension extends \Twig_Extension {
    /**
     * @param \DateTime $expiresAt
     * @return string
     */
    public function getFilters(){
        return [
            new \Twig_SimpleFilter("expireDate", [$this, "expireDate"])
        ];
    }
    /**
     * @param \DateTime $expiresAt
     * @return string
     */
    public function getFunctions(){
        return[
            new \Twig_SimpleFunction("auctionStyle",[$this,"auctionStyle"])
        ];
    }

    public function expireDate(\DateTime $expiresAt){
        if($expiresAt < new \DateTime("-7 days")){
            return $expiresAt->format("Y-m-d H:i");
        }
        if($expiresAt > new \DateTime("-1 day")){
            return "za ".$expiresAt->diff(new \DateTime())->days ."dni";
        }
        return "za " .$expiresAt->format("H")." godz. ". $expiresAt->format("i")." min.";
    }

    public function auctionStyle(\DateTime $expiresAt){
        if($expiresAt < new \DateTime("-1 day")){
            return "panel-danger";
        }
        return "panel-default";
    }

}
 ?>
