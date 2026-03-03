<?php

namespace Jtl\Connector\Vivino\Models;

use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\ManyToMany;

/**
 * @Entity @Table(name="products")
 */
class Product {
    /**
     * @Id @GeneratedValue @Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @Column(type="datetime", columnDefinition="TIMESTAMP DEFAULT CURRENT_TIMESTAMP")
     */
    private ?DateTime $created = null;

    /**
     * @Column(type="datetime", columnDefinition="TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP")
     */
    private ?DateTime $updated = null;

    /**
     * @Column(type="integer")
     */
    private ?int $jtlId = null;

    /**
     * @Column(type="string",name="sku")
     */
    private string $sku;

    /**
     * @Column(type="integer",name="stock")
     */
    private string $stock;

    /**
     * @Column(type="string",name="product_name")
     */
    private string $productName;

    /**
     * @Column(type="string",name="vivino_name")
     */
    private string $vivinoName;

    /**
     * @Column(type="float",name="bottle_price")
     */
    private float $bottlePrice;

    /**
     * @Column(type="float",name="bottle_size")
     */
    private float $bottleSize;

    /**
     * @Column(type="integer",name="bottle_quantity")
     */
    private int $bottleQuantity;

    /**
     * @Column(type="string")
     */
    private string $link;

    /**
     * @Column(type="string")
     */
    private string $image;

    /**
     * @Column(type="string")
     */
    private string $ean;

    /**
     * @Column(type="string",name="wine_vintage")
     */
    private string $wineVintage;

    /**
     * @Column(type="string",name="wine_color")
     */
    private string $wineColor;

    /**
     * @Column(type="string")
     */
    private string $country;

    /**
     * @Column(type="string")
     */
    private string $lbmZutaten;

    /**
     * @Column(type="float")
     */
    private float $alkohol;

    /**
     * @Column(type="float")
     */
    private float $restzucker;

    /**
     * @Column(type="float",name="lbm_brennwert_kj")
     */
    private float $lbmBrennwertKj;


/*


saeure
*/
    /**
     * @Column(type="boolean",name="allergen_sulfite")
     */
    private bool $allergenSulfite;
    /**
     * @Column(type="boolean",name="allergen_milch")
     */
    private bool $allergenMilch;
    /**
     * @Column(type="boolean",name="allergen_ei")
     */
    private bool $allergenEi;
    /**
     * @Column(type="boolean",name="allergen_erdnuss")
     */
    private bool $allergenErdnuss;
    /**
     * @Column(type="boolean",name="allergen_fisch")
     */
    private bool $allergenFisch;
    /**
     * @Column(type="boolean",name="allergen_albumin")
     */
    private bool $allergenAlbumin;
    /**
     * @Column(type="boolean",name="allergen_gluten")
     */
    private bool $allergenGluten;
    /**
     * @Column(type="boolean",name="allergen_kasein")
     */
    private bool $allergenKasein;
    /**
     * @Column(type="boolean",name="allergen_krebstier")
     */
    private bool $allergenKrebstier;
    /**
     * @Column(type="boolean",name="allergen_lupinen")
     */
    private bool $allergenLupinen;
    /**
     * @Column(type="boolean",name="allergen_lysozym")
     */
    private bool $allergenLysozym;
    /**
     * @Column(type="boolean",name="allergen_nuss")
     */
    private bool $allergenNuss;
    /**
     * @Column(type="boolean",name="allergen_sellerie")
     */
    private bool $allergenSellerie;
    /**
     * @Column(type="boolean",name="allergen_senf")
     */
    private bool $allergenSenf;
    /**
     * @Column(type="boolean",name="allergen_sesam")
     */
    private bool $allergenSesam;
    /**
     * @Column(type="boolean",name="allergen_soja")
     */
    private bool $allergenSoja;
    /**
     * @Column(type="boolean",name="allergen_weichtier")
     */
    private bool $allergenWeichtier;
    /**
     * @Column(type="boolean",name="allergen_farbstoffe")
     */
    private bool $allergenFarbstoffe;
    /**
     * @Column(type="boolean",name="allergen_aromen")
     */
    private bool $allergenAromen;
    /**
     * @Column(type="boolean",name="allergen_konservierungsstoffe")
     */
    private bool $allergenKonservierungsstoffe;
    /**
     * @Column(type="boolean",name="allergen_antioxidanzien")
     */
    private bool $allergenAntioxidanzien;


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get created.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Get updated.
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set jtlId.
     *
     * @param int $jtlId
     *
     * @return Product
     */
    public function setJtlId($jtlId)
    {
        $this->jtlId = $jtlId;

        return $this;
    }

    /**
     * Get jtlId.
     *
     * @return int
     */
    public function getJtlId()
    {
        return $this->jtlId;
    }

    /**
     * Set sku.
     *
     * @param string $sku
     *
     * @return Product
     */
    public function setSku($sku)
    {
        $this->sku = $sku;

        return $this;
    }

    /**
     * Get sku.
     *
     * @return string
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * Set sku.
     *
     * @param string $stock
     *
     * @return Product
     */
    public function setStock($stock)
    {
        $this->stock = $stock;

        return $this;
    }

    /**
     * Get sku.
     *
     * @return ineger
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * Set productName.
     *
     * @param string $productName
     *
     * @return Product
     */
    public function setProductName($productName)
    {
        $this->productName = $productName;

        return $this;
    }

    /**
     * Get productName.
     *
     * @return string
     */
    public function getProductName()
    {
        return $this->productName;
    }


    /**
     * Set vivinoName.
     *
     * @param string $vivinoName
     *
     * @return Product
     */
    public function setVivinoName($vivinoName)
    {
        $this->vivinoName = $vivinoName;

        return $this;
    }

    /**
     * Get vivinoName.
     *
     * @return string
     */
    public function getVivinoName()
    {
        return $this->vivinoName;
    }

    /**
     * Set bottlePrice.
     *
     * @param float $bottlePrice
     *
     * @return Product
     */
    public function setBottlePrice($bottlePrice)
    {
        $this->bottlePrice = $bottlePrice;

        return $this;
    }

    /**
     * Get bottlePrice.
     *
     * @return float
     */
    public function getBottlePrice()
    {
        return $this->bottlePrice;
    }

    /**
     * Set bottleSize.
     *
     * @param float $bottleSize
     *
     * @return Product
     */
    public function setBottleSize($bottleSize)
    {
        $this->bottleSize = $bottleSize;

        return $this;
    }

    /**
     * Get bottleSize.
     *
     * @return float
     */
    public function getBottleSize()
    {
        return $this->bottleSize;
    }

    /**
     * Set bottleQuantity.
     *
     * @param int $bottleQuantity
     *
     * @return Product
     */
    public function setBottleQuantity($bottleQuantity)
    {
        $this->bottleQuantity = $bottleQuantity;

        return $this;
    }

    /**
     * Get bottleQuantity.
     *
     * @return int
     */
    public function getBottleQuantity()
    {
        return $this->bottleQuantity;
    }

    /**
     * Set link.
     *
     * @param string $link
     *
     * @return Product
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link.
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set image.
     *
     * @param string $image
     *
     * @return Product
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image.
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set ean.
     *
     * @param string $ean
     *
     * @return Product
     */
    public function setEan($ean)
    {
        $this->ean = $ean;

        return $this;
    }

    /**
     * Get ean.
     *
     * @return string
     */
    public function getEan()
    {
        return $this->ean;
    }

    /**
     * Set wineVintage.
     *
     * @param string $wineVintage
     *
     * @return Product
     */
    public function setWineVintage($wineVintage)
    {
        $this->wineVintage = $wineVintage;

        return $this;
    }

    /**
     * Get wineVintage.
     *
     * @return string
     */
    public function getWineVintage()
    {
        return $this->wineVintage;
    }

    /**
     * Set wineColor.
     *
     * @param string $wineColor
     *
     * @return Product
     */
    public function setWineColor($wineColor)
    {
        $this->wineColor = $wineColor;

        return $this;
    }

    /**
     * Get wineColor.
     *
     * @return string
     */
    public function getWineColor()
    {
        return $this->wineColor;
    }

    /**
     * Set country.
     *
     * @param string $country
     *
     * @return Product
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country.
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set lbmZutaten.
     *
     * @param string $lbmZutaten
     *
     * @return Product
     */
    public function setLbmZutaten($lbmZutaten)
    {
        $this->lbmZutaten = $lbmZutaten;

        return $this;
    }

    /**
     * Get lbmZutaten.
     *
     * @return string
     */
    public function getLbmZutaten()
    {
        return $this->lbmZutaten;
    }

    /**
     * Set alkohol.
     *
     * @param float $alkohol
     *
     * @return Product
     */
    public function setAlkohol($alkohol)
    {
        $this->alkohol = $alkohol;

        return $this;
    }

    /**
     * Get alkohol.
     *
     * @return float
     */
    public function getAlkohol()
    {
        return $this->alkohol;
    }

    /**
     * Set restzucker.
     *
     * @param float $restzucker
     *
     * @return Product
     */
    public function setRestzucker($restzucker)
    {
        $this->restzucker = $restzucker;

        return $this;
    }

    /**
     * Get restzucker.
     *
     * @return float
     */
    public function getRestzucker()
    {
        return $this->restzucker;
    }

    /**
     * Set lbmBrennwertKj.
     *
     * @param float $lbmBrennwertKj
     *
     * @return Product
     */
    public function setLbmBrennwertKj($lbmBrennwertKj)
    {
        $this->lbmBrennwertKj = $lbmBrennwertKj;

        return $this;
    }

    /**
     * Get lbmBrennwertKj.
     *
     * @return float
     */
    public function getLbmBrennwertKj()
    {
        return $this->lbmBrennwertKj;
    }


    public function getAllergenSulfite() {
        return $this->allergenSulfite;
    }
    public function setAllergenSulfite( $allergenSulfite ) {
        $this->allergenSulfite = $allergenSulfite;
        return $this;
    }
    public function getAllergenMilch() {
        return $this->allergenMilch;
    }
    public function setAllergenMilch( $allergenMilch ) {
        $this->allergenMilch = $allergenMilch;
        return $this;
    }
    public function getAllergenEi() {
        return $this->allergenEi;
    }
    public function setAllergenEi( $allergenEi ) {
        $this->allergenEi = $allergenEi;
        return $this;
    }
    public function getAllergenErdnuss() {
        return $this->allergenErdnuss;
    }
    public function setAllergenErdnuss( $allergenErdnuss ) {
        $this->allergenErdnuss = $allergenErdnuss;
        return $this;
    }
    public function getAllergenFisch() {
        return $this->allergenFisch;
    }
    public function setAllergenFisch( $allergenFisch ) {
        $this->allergenFisch = $allergenFisch;
        return $this;
    }
    public function getAllergenAlbumin() {
        return $this->allergenAlbumin;
    }
    public function setAllergenAlbumin( $allergenAlbumin ) {
        $this->allergenAlbumin = $allergenAlbumin;
        return $this;
    }
    public function getAllergenGluten() {
        return $this->allergenGluten;
    }
    public function setAllergenGluten( $allergenGluten ) {
        $this->allergenGluten = $allergenGluten;
        return $this;
    }
    public function getAllergenKasein() {
        return $this->allergenKasein;
    }
    public function setAllergenKasein( $allergenKasein ) {
        $this->allergenKasein = $allergenKasein;
        return $this;
    }
    public function getAllergenKrebstier() {
        return $this->allergenKrebstier;
    }
    public function setAllergenKrebstier( $allergenKrebstier ) {
        $this->allergenKrebstier = $allergenKrebstier;
        return $this;
    }
    public function getAllergenLupinen() {
        return $this->allergenLupinen;
    }
    public function setAllergenLupinen( $allergenLupinen ) {
        $this->allergenLupinen = $allergenLupinen;
        return $this;
    }
    public function getAllergenLysozym() {
        return $this->allergenLysozym;
    }
    public function setAllergenLysozym( $allergenLysozym ) {
        $this->allergenLysozym = $allergenLysozym;
        return $this;
    }
    public function getAllergenNuss() {
        return $this->allergenNuss;
    }
    public function setAllergenNuss( $allergenNuss ) {
        $this->allergenNuss = $allergenNuss;
        return $this;
    }
    public function getAllergenSellerie() {
        return $this->allergenSellerie;
    }
    public function setAllergenSellerie( $allergenSellerie ) {
        $this->allergenSellerie = $allergenSellerie;
        return $this;
    }
    public function getAllergenSenf() {
        return $this->allergenSenf;
    }
    public function setAllergenSenf( $allergenSenf ) {
        $this->allergenSenf = $allergenSenf;
        return $this;
    }
    public function getAllergenSesam() {
        return $this->allergenSesam;
    }
    public function setAllergenSesam( $allergenSesam ) {
        $this->allergenSesam = $allergenSesam;
        return $this;
    }
    public function getAllergenSoja() {
        return $this->allergenSoja;
    }
    public function setAllergenSoja( $allergenSoja ) {
        $this->allergenSoja = $allergenSoja;
        return $this;
    }
    public function getAllergenWeichtier() {
        return $this->allergenWeichtier;
    }
    public function setAllergenWeichtier( $allergenWeichtier ) {
        $this->allergenWeichtier = $allergenWeichtier;
        return $this;
    }
    public function getAllergenFarbstoffe() {
        return $this->allergenFarbstoffe;
    }
    public function setAllergenFarbstoffe( $allergenFarbstoffe ) {
        $this->allergenFarbstoffe = $allergenFarbstoffe;
        return $this;
    }
    public function getAllergenAromen() {
        return $this->allergenAromen;
    }
    public function setAllergenAromen( $allergenAromen ) {
        $this->allergenAromen = $allergenAromen;
        return $this;
    }
    public function getAllergenKonservierungsstoffe() {
        return $this->allergenKonservierungsstoffe;
    }
    public function setAllergenKonservierungsstoffe( $allergenKonservierungsstoffe ) {
        $this->allergenKonservierungsstoffe = $allergenKonservierungsstoffe;
        return $this;
    }
    public function getAllergenAntioxidanzien() {
        return $this->allergenAntioxidanzien;
    }
    public function setAllergenAntioxidanzien( $allergenAntioxidanzien ) {
        $this->allergenAntioxidanzien = $allergenAntioxidanzien;
        return $this;
    }
}
