<?php

namespace AppBundle\Controller;
use AppBundle\Entity\Auction;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormError;
use AppBundle\Form\AuctionType;
use AppBundle\Form\BidType;
use AppBundle\Service\DateService;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;




class AuctionController extends Controller
{

/**
 * @Route("/", name="auction_index")
 *
 * @return Response;
 *
 */

public function indexAction()
    {

        $entityManager = $this->getDoctrine()->getManager();
    //    $auctions = $entityManager->getRepository(Auction::class)->findAll();
       $auctions =$entityManager->getRepository(Auction::class)->findActiveOrdered();
      // ->findBy(["status" => Auction::STATUS_ACTIVE]);

     $logger = $this->get("logger");
     $logger->info("Użytkownik wszedł do akcji index.");
     $dateservice = $this->get(DateService::class);
     $logger->info("Aktualny dzien miesiaca to: " . $dateservice->getDay(new \DateTime()));


    return $this->render("Auction/index.html.twig", ["auctions" => $auctions]);
    }


    /**
     * [detailsAction description]
     * @Route("/auction/detailis/{id}", name="auction_details")
     * return Response;
     * @param Auction $auction;
     */
public function detailsAction(Auction $auction){
    //Auction $auction sprawia, że poniższy fragemnt jest zbędny
    //$entityManager = $this->getDoctrine()->getManager();
    //$auction = $entityManager->getRepository(Auction::class)->findOneBy(["id" => $id]);

if ($auction->getStatus() === Auction::STATUS_FINSHED){
    return $this->render("Auction/finished.html.twig", ["auction" => $auction]);
}
    $buyForm = $this->createFormBuilder()
    ->setAction($this->generateUrl("offer_buy", ["id" =>$auction->getId()]))
    ->add("submit",SubmitType::class,["label" => "Kup"])
    ->getForm();

    $bidForm = $this->createForm(BidType::class, null,
     ["action" => $this->generateUrl("offer_bid", ["id" => $auction->getId()])]
 );

    return $this->render(
        "Auction/details.html.twig",
        ["auction" => $auction,
        "buyForm" => $buyForm->createView(),
        "bidForm" => $bidForm->createView(),

    ]);

}


/**
 * @Route("/auction/finish/{id}", name="auction_finish" ,methods={"POST"})
 * @param  Auction $auction
 * @return RedirectResponse
 */
public function finishAuction(Auction $auction){
    $this->denyAccessUnlessGranted("ROLE_USER");

    if($this->getUser() !== $auction->getOwner()){
        throw new AccessDeniedException();

    }
    $auction
    ->setExpiresAt(new \DateTime())
    ->setStatus(Auction::STATUS_FINSHED);

    $entityManager = $this->getDoctrine()->getManager();
    $entityManager->persist($auction);
    $entityManager->flush();

    $this->addFlash("succes", "Aukcja: {$auction->getTitle()} została zakoczona.");

    return $this->redirectToRoute("auction_details", ["id" => $auction->getId()]);

}

}

 ?>
