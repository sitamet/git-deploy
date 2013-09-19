<?php
// Make sure we have a payload, stop if we do not.
if( ! isset( $_POST['payload'] ) )
	die( '<h1>No payload present</h1><p>A BitBucket POST payload is required to deploy from this script.</p>' );

/**
 * Tell the script this is an active end point.
 */
define( 'ACTIVE_DEPLOY_ENDPOINT', true );

require_once 'deploy-config.php';
/**
 * Deploys BitBucket git repos
 */
class BitBucket_Deploy extends Deploy {
	/**
	 * Decodes and validates the data from bitbucket and calls the 
	 * doploy contructor to deoploy the new code.
	 *
	 * @param 	string 	$payload 	The JSON encoded payload data.
	 */
	function __construct( $payload ) {
		$payload = json_decode( stripslashes( $_POST['payload'] ), true );
		$name = $payload['repository']['name'];

        // bitbucket bug: https://bitbucket.org/site/master/issue/5938/include-branch-and-tag-information-for
        // when payload carries multiple commits, branch name can not be in first commit

        $branchName = $this->getCommitsBranchName($payload['commits']);

		$this->log( $branchName );
		if ( isset( parent::$repos[ $name ] ) && parent::$repos[ $name ]['branch'] === $branchName ) {
			$data = parent::$repos[ $name ];
			$data['commit'] = $payload['commits'][0]['node'];
			parent::__construct( $name, $data );
		}
	}


    private function getCommitsBranchName($commits) {
        $branchName = null;

        while((list(, $commit) = each($commits)) && $branchName == null) {
            if ($commit['branch'] !== null)  $branchName = $commit['branch'];
        }

        return $branchName;
    }

}
// Start the deploy attempt.
new BitBucket_Deploy( $_POST['payload'] );