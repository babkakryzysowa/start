<?php
namespace AppBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @var Offer[]\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Offer", mappedBy="owner")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id")
     */
    private $offers;

    /**
     * @var Auction[]\ArrayCollection
     * @ORM\OneToMany(targetEntity="Auction", mappedBy="owner")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id")
     */
    private $auctions;
    public function __construct(){
        parent::__construct();
        $this->auctions = new ArrayCollection();
        $this->offers = new ArrayCollection();
    }
/**
 *
 * @return Auction[]\ArrayCollection
 */
    public function getAuctions(){
        return $this->auctions;
    }
/**
 *
 * @param Auction $auction [description]
 * @return $this
 */
    public function addAuction(Auction $auction){
        $this->auctions[] = $auction;
        return $this;
    }
    /**
     * @return Offer[]\ArrayCollection
     */
    public function getOffers(){
        return $this->offers();
    }
    /**
     *
     * @param Offer $offer [description]
     * @return $this
     */
    public function addOffer(Offer $offer){
        $this->offers = $offer;
    }

}
