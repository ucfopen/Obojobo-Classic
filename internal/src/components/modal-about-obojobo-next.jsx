import './modal-about-obojobo-next.scss'

import Button from './button'
import React from 'react'
import PropTypes from 'prop-types'
import RepositoryModal from './repository-modal'

export default function ModalAboutObojoboNext({ onClose }) {
	return (
		<RepositoryModal
			className="aboutObojoboNext"
			onCloseModal={onClose}
		>
			<div className="repository--modal-about-obojobo-next">
				<p>
					Since Adobe Flash is retiring at the end of 2020 we&apos;ve made some changes to ensure that
					Obojobo content will keep working well into 2021 and beyond. For details on these changes
					expand the items below.
				</p>

				<h1>Flash is going away</h1>

				<details>
					<summary>What&apos;s changed?</summary>
					<p>
						The Repository interface has been rewritten to remove any requirement for Flash. This
						updated streamlined interface replaces the <b>My Instances</b> section and supports all of
						the existing features for managing instances except for the Visit Visualizer.
					</p>
				</details>

				<details>
					<summary>Where&apos;s the Info-lit and the other public library modules?</summary>
					<p>
						The Public Library section is no longer accessible from the repository, but you can still
						create these modules via an external tool assignment in Canvas / Webcourses.{' '}
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
						removed. However, we&apos;ve been hard at work at a ground-up rewrite of the next
						iteration of Obojobo called <b>Obojobo Next</b> which is now available campus wide at UCF.
						Likewise, Obojobo will continue to be available but is being renamed to{' '}
						<b>Obojobo Classic</b>.
					</p>

					<p>
						If you&apos;ve authored your own content in Obojobo or have been interested in doing so,
						we highly recommend you check it out!
					</p>
				</details>

				<h1>Obojobo Next is coming</h1>

				<details>
					<summary>What&apos;s different with Obojobo Next?</summary>

					<p>
						Obojobo Next is the next iteration of Obojobo. Modules are now less restrictive and
						content can flow across multiple pages with practice questions in-line with the content.
						Authoring modules in Obojobo Next has been streamlined and now editing is more like typing
						up a Word document. Assessments are now optional and have more scoring options, allowing
						you to assign a passing threshold score (among other options).
					</p>

					<p>
						UCF is actively developing Obojobo Next, with new features being added monthly. For a
						complete detailed list of the differences between Obojobo Next and Obojobo Classic check
						out this page with a comparison chart.
					</p>
				</details>

				<details>
					<summary>Can I use Obojobo Next now?</summary>

					<p>
						Yes - it&apos;s now available campus wide. Both Obojobo Next and Obojobo Classic will
						coexist simultaneously.
					</p>
				</details>

				<details>
					<summary>Will modules created in Obojobo Classic work in Obojobo Next?</summary>

					<p>
						No - the two systems are separate and modules can&apos;t automatically convert between the
						two systems.
					</p>
				</details>

				<Button text="Close" type="text" onClick={onClose} />
			</div>
		</RepositoryModal>
	)
}

ModalAboutObojoboNext.propTypes = {
	onClose: PropTypes.func.isRequired
}
