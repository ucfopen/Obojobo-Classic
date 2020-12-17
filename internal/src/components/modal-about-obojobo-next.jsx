import './modal-about-obojobo-next.scss'

import Button from './button'
import React from 'react'
import PropTypes from 'prop-types'
import RepositoryModal from './repository-modal'

export default function ModalAboutObojoboNext({ onClose }) {
	return (
		<RepositoryModal className="aboutObojoboNext" onCloseModal={onClose}>
			<div className="repository--modal-about-obojobo-next">
				<p>
					Since Adobe Flash is retiring at the end of 2020 we&apos;ve made some changes to ensure
					that Obojobo content will keep working well into 2021 and beyond. For details on these
					changes expand the items below.
				</p>

				<details>
					<summary>What&apos;s changed?</summary>
					<p>
						The Repository interface has been rewritten to remove any requirement for Flash. This
						updated streamlined interface replaces the <b>My Instances</b> section and supports all
						of the existing features for managing instances except for the Visit Visualizer.
					</p>
				</details>

				<details>
					<summary>Where&apos;s the Info-lit and the other public library modules?</summary>
					<p>
						There are 2 ways to create instances of modules. The popular option is to create a
						Canvas assignment or module page and select external tool (supports automatic scores
						sync). The other option is to use the new instance button here and copy/paste the link
						(no automatic sync, download scores in csv format).{' '}
						<a
							target="_blank"
							rel="noreferrer"
							href="https://cdl.ucf.edu/support/webcourses/obojobo/learning-objects-webcourses/"
						>
							See this guide for information on how to do it
						</a>
						.
					</p>
				</details>

				<details>
					<summary>What about my modules?</summary>

					<p>
						Moving forward to 2021 and beyond with the retirement of Flash, the creation of Obojobo
						modules has been disabled. Additionally the My Objects and Media sections have been
						removed.
					</p>
				</details>

				<details>
					<summary>The retired Flash repository</summary>

					<p>
						Again, Adobe and the browsers will have completely retired Flash by 2021.  However, if you need
						access to the legacy Flash based repository,{' '}
						<a
							href="/repository-flash.php"
							target="obojobo-repository"
						>
							it's still available here
						</a>
					</p>
				</details>

				<p className="about-obo-next">
					<b>Obojobo Next</b> is here! We&apos;re hard at work on the a huge ground-up redesign.
					<br />{' '}
					<a
						href="https://github.com/ucfopen/Obojobo/wiki/Obojobo-Classic-To-Obojobo-Next-Transition"
						target="obojobo-transition"
					>
						Learn more about the transition on our Wiki.
					</a>
				</p>

				<Button text="Close" type="small" onClick={onClose} />
			</div>
		</RepositoryModal>
	)
}

ModalAboutObojoboNext.propTypes = {
	onClose: PropTypes.func.isRequired
}
