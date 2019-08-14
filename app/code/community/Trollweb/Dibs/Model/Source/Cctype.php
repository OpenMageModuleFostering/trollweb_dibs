<?php
/**
 * DIBS Payment module
 *
 * LICENSE AND USAGE INFORMATION
 * It is NOT allowed to modify, copy or re-sell this file or any
 * part of it. Please contact us by email at support@trollweb.no or
 * visit us at www.trollweb.no if you have any questions about this.
 * Trollweb is not responsible for any problems caused by this file.
 *
 * Visit us at http://www.trollweb.no today!
 *
 * @category   Trollweb
 * @package    Trollweb_Dibs
 * @copyright  Copyright (c) 2013 Trollweb (http://www.trollweb.no)
 * @license    Single-site License
 *
 */

class Trollweb_Dibs_Model_Source_Cctype
{
    public function toOptionArray() {
        $options =  array();
        foreach ($this->getCcTypes() as $code => $name) {
            $options[] = array(
               'value' => $code,
               'label' => $name
            );
        }

        return $options;
    }

    private function getCcTypes() {
        return array(
            'MC'=>'Mastercard',
            'VISA'=>'VISA',
            'AMEX'=>'American Express',
            'DIN'=>'Diners Club',
            'AAK'=>'Århus city kort',
            'ACCEPT'=>'Accept card',
            'ACK'=>'Albertslund Centrum Kundekort',
            'AKK'=>'Apollo-/Kuonikonto',
            'AMEX'=>'American Express',
            'AMEX(DK)'=>'American Express (Danish card)',
            'AMEX(SE)'=>'American Express (Swedishcard)',
            'BHBC'=>'Bauhaus Best card',
            'CCK'=>'Computer City Customer Card',
            'DAELLS'=>'Daells Bolighus Kundekort',
            'DIN(DK)'=>'Diners Club (Danish card)',
            'DK'=>'Dankort',
            'ELEC'=>'VISA Electron',
            'EWORLD'=>'Electronic World Credit Card',
            'FCC'=>'Ford Credit Card',
            'FCK'=>'Frederiksberg Centret Kundekort',
            'FFK'=>'Forbrugsforeningen Card',
            'FINX(SE)'=>'Finax (SE)',
            'FISC'=>'Fields Shoppingcard',
            'FLEGCARD'=>'Fleggard kort',
            'FSC'=>'Fisketorvet Shopping Card',
            'GIT'=>'Getitcard',
            'GSC'=>'Glostrup Shopping Card',
            'GRA'=>'Graphium',
            'HEME'=>'Hemtex faktura',
            'HEMP'=>'Hemtex personalkort',
            'HEMTX'=>'Hemtex clubkort',
            'HMK'=>'HM Konto (Hennes og Mauritz)',
            'HNYBORG'=>'Harald Nyborg',
            'HSC'=>'Hillerød Shopping Card',
            'HTX'=>'Hydro Texaco',
            'IBC'=>'Inspiration Best Card',
            'IKEA'=>'IKEA kort',
            'ISHBY'=>'Sparbank Vestkort',
            'JCB'=>'JCB (Japan Credit Bureau)',
            'JEM_FIX'=>'Jem&Fix Kundekort',
            'KAUPBK'=>'Kaupthing Bankkort',
            'LFBBK'=>'LänsförsäkringarBank Bankkort',
            'LIC(DK)'=>'Lærernes IndkøbsCentral (Denmark)',
            'LIC(SE)'=>'Lærernes IndkøbsCentral (Sweden)',
            'LOPLUS'=>'LO Plus Guldkort',
            'MC(DK)'=>'Mastercard (Danish card)',
            'MC(SE)'=>'Mastercard (Swedish card)',
            'MC(YX)'=>'YX Mastercard',
            'MEDM'=>'Medmera',
            'MERLIN'=>'Merlin Kreditkort',
            'MTRO'=>'Maestro',
            'MTRO(DK)'=>'Maestro (DK)',
            'MTRO(UK)'=>'Maestro (UK)',
            'MTRO(SOLO)'=>'Solo',
            'MTRO(SE)'=>'Maestro (SE)',
            'MYHC'=>'My Holiday Card',
            'NSBK'=>'Nordea Bankkort',
            'OESBK'=>'Östgöta EnskildaBankkort',
            'PayPal'=>'Paypal',
            'Q8SK'=>'Q8 ServiceKort',
            'REB'=>'Resurs Bank',
            'REMCARD'=>'Remember Card',
            'ROEDCEN'=>'Rødovre Centerkort',
            'SBSBK'=>'Skandiabanken Bankkort',
            'SEB_KOBK'=>'SEB Köpkort',
            'SEBSBK'=>'SEB Bankkort',
            'SHB'=>'HandelsbankenKöpkort',
            'SILV_ERHV'=>'Silvan Konto Erhverv',
            'SILV_PRIV'=>'Silvan Konto Privat',
            'STARTOUR'=>'Star Tour',
            'TLK'=>'Tæppeland',
            'TUBC'=>'Toys R Us - BestCard',
            'V(DK)'=>'VISA-Dankort',
            'VEKO'=>'VEKO Finans',
            'VISA(DK)'=>'VISA (DK)',
            'VISA(SE)'=>'VISA (SE)',
            'WOCO'=>'Wonderful Copenhagen Card'
        );
    }
}
