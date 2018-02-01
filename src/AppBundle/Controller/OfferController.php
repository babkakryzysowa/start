<?php
namespace AppBundle\Controller;
use AppBundle\Entity\Auction;
use AppBundle\Entity\Offer;
use AppBundle\Form\BidType;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;


class OfferController extends Controller {
    /**
     * @Route("/auction/buy/{id}", name="offer_buy", methods={"POST"})
     * @param  Auction $auction
     * @return RedirectResponse
     */
    public function buyAuction(Auction $auction){
        $this->denyAccessUnlessGranted("ROLE_USER");
        $offer = new Offer();
        $offer
            ->setAuction($auction)
            ->setType(Offer::TYPE_BUY)
            ->setPrice($auction->getPrice());
            $auction->setStatus(Auction::STATUS_FINSHED);
            $auction->setExpiresAt(new \DateTime());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($auction);
            $entityManager->persist($offer);
            $entityManager->flush();
            $this->addFlash("succes", "Kupiłeś: {$auction->getTitle()} za kwotę {$offer->getPrice()} zł.");

            return $this->redirectToRoute("auction_details", ["id" => $auction->getId()]);
    }
/**
 * @Route("/auction/bid/{id}", name="offer_bid", methods={"POST"})
 * @param  Request $requerst [description]
 * @param  Auction $auction  [description]
 * @return RedirectResposne;
 */
    public function bidAuction(Request $request, Auction $auction){
        $this->denyAccessUnlessGranted("ROLE_USER");

        $offer = new Offer();
        $bidForm = $this->createForm(BidType::class, $offer);

        $bidForm->handleRequest($request);

        if($bidForm->isValid()){
        $entityManager = $this->getDoctrine()->getManager();
        $lastOffer = $entityManager
        ->getRepository(Offer::class)
        ->findOneBy(["auction" => $auction], ["createdAt" => "DESC"]);

        if(isset($lastOffer)){
            if($offer->getPrice() <= $lastOffer->getPrice()){
                $this->addFlash("error", "Twoja oferta musi być większa od {$lastOffer->getPrice()} zł");
                return $this->redirectToRoute("auction_details", ["id" => $auction->getId()]);

            }
        }else{
            if($offer->getPrice() < $auction->getStartingPrice() ){
                $this->addFlash("error", "Twoja oferta nie może być niższa od ceny wywoławczej!");
                return $this->redirectToRoute("auction_details", ["id" => $auction->getId()]);


            }
        }

        $offer
        ->setType(Offer::TYPE_BID)
        ->setAuction($auction);
        $entityManager->persist($offer);
        $entityManager->flush();
        $this->addFlash("succes", "Złożyłeś ofertę na przedmiot: {$auction->getTitle()} na kwotę {$offer->getPrice()} zł.");

    }else {
        $this->addFlash("error", "Błąd podczas składania oferty na przedmiot: {$auction->getTitle()} na kwotę: {$offer->getPrice()} zł.");

    }
    return $this->redirectToRoute("auction_details", ["id" => $auction->getId()]);

    }
}
 ?>
