<?php

require_once(__DIR__ . "/BLActionExecutor.class.php");

/**
 * Encadre les traitements fonctionnels d'une action de connecteur en prenant en charge :
 * - la d�tection des erreurs d'acc�s aux services et le pilotage des 
 *   suspension/reprises du connecteur concern�
 * - la d�tection des services d�sactiv�s
 * - le log dans le journal, pour les actions API
 * Offre des m�thodes d�clenchables par les actions d�riv�es : 
 * - �mission de notification
 * Restent � la charge du fonctionnel :
 * - le traitement, acc�dant au(x) service(s)
 * - le calcul du r�sultat (message, informations, ...)
 * - red�finition du formattage d'affichage par d�faut, pour les actions d'obtention d'information
 */
abstract class BLConnecteurActionExecutor extends BLActionExecutor {

    // Valeurs conventionn�es pour GO_KEY_*

    const GO_ETAT_SUCCES = 'etat-ok';
    const GO_ETAT_ECHEC = 'etat-nok';

    /**
     * Ex�cute le traitement fonctionnel de l'action.
     * Les conventions de retours sont d�crites par chaque impl�mentation.
     * <p>
     * En cas d'exception, l'action est consid�r�e en �chec. 
     * Le texte de l'exception sert de message.
     * <br>
     * En cas d'absence d'exception, le retour d�termine le r�sultat de l'action.
     * <p>
     * @return mixed bool�en, string ou array
     *         <ul>
     *         <li>bool�en
     *              true pour un succ�s, false pour un �chec.<br>
     *              Le texte du message est lu dans @link self::getLastMessage
     *              </li>
     *         <li>string
     *              L'action est un succ�s. Le retour est le texte du message.
     *              </li>
     *         <li>array
     *              <ul>
     *              <li>GO_KEY_ETAT => r�sultat de l'action<br>
     *                  GO_ETAT_SUCCES ou true pour un succ�s<br>
     *                  GO_ETAT_ECHEC ou false pour un �chec<br>
     *                  </li>
     *              <li>GO_KEY_MESSAGE => message du r�sultat.<br>
     *                  </li>
     *              </ul>
     *              </li>
     *         </ul>
     */
    abstract protected function goFonctionnel();

    public function go() {
        try {
            $gof = $this->goFonctionnel();
            if (is_array($gof)) {
                $gofEtat = $gof[self::GO_KEY_ETAT];
                if ($gofEtat == self::GO_ETAT_SUCCES || $gofEtat === true) {
                    $gofEtat = true;
                } elseif ($gofEtat == self::GO_ETAT_ECHEC || $gofEtat === false) {
                    $gofEtat = false;
                } else {
                    throw new Exception("Format de retour 'goFonctionnel' incorrect");
                }
                $gofMessage = $gof[self::GO_KEY_MESSAGE];
            } elseif (is_string($gof)) {
                $gofEtat = true;
                $gofMessage = $gof;
            } else {
                $gofEtat = $gof;
                $gofMessage = $this->getLastMessage();
            }
            $this->setLastMessage($gofMessage);
            return $gofEtat;
        } catch (ConnecteurAccesException $gofEx) {
            // Gestion des suspensions
            try {
                $this->objectInstancier->ConnecteurSuspensionControler->onAccesEchec($gofEx->getConnecteur(), $gofEx);
            } catch (Exception $onAccesEchecEx) {
                // Erreur de gestion des suspensions => erreur trac�e
                $this->throwException($onAccesEchecEx);
            }
            // Erreur trac�e
            $this->throwException($gofEx);
        } catch (Exception $gofEx) {
            // Erreur fonctionnelle => erreur trac�e, �tat d'erreur
            $this->throwException($gofEx);
        }
    }

    private function throwException(Exception $ex) {
        $messageDetail = parent::exceptionToJson($ex);
        $connecteurConfig = $this->getConnecteurProperties();
        $connecteurConfig->addFileFromData(self::ATTR_ERREUR_DETAIL, 'erreur_detail', $messageDetail);
        throw $ex;
    }

}
