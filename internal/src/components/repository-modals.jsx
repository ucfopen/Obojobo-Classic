import './repository-modals.scss'

import React, { useEffect } from 'react'
import ReactDom from 'react-dom'
import ReactModal from 'react-modal'
import ModalAboutLO from './modal-about-lo'
import Button from './button'
import ModalInstanceDetails from './modal-instance-details'
import ModalScoreDetails from './modal-score-details'
import ModalScoresByQuestion from './modal-scores-by-question'
import ModalAboutObojoboNext from './modal-about-obojobo-next'
import { useQuery, useQueryCache } from 'react-query'
import { apiGetLOMeta, apiGetLO, apiGetInstanceTrackingData, apiGetVisitTrackingData } from '../util/api'


const ModalAboutLOWithAPI = ({onClose, loID}) => {
	const { isError, data, isFetching } = useQuery(['getLoMeta', loID], apiGetLOMeta, {
		initialStale: true,
		staleTime: Infinity,
	})

	const props = React.useMemo(() => {
		if(isFetching || isError) return {}
		const {learnTime, languageID, notes, summary, objective } = data
		const {contentSize, practiceSize, assessmentSize} = summary
		return {
			learnTime,
			languageID,
			contentSize,
			practiceSize,
			assessmentSize,
			notes,
			objective
		}
	}, [onClose, loID, data, isFetching])

	if(isFetching) return null
	if(isError) return <div>Error Loading Data</div>
	return <ModalAboutLO {...props} onClose={onClose} />
}

const extractAssessmentAttemptData = (logs, aGroup) => {
	const foundLogs = []
	logs.forEach(log => {
		if (log.itemType === 'StartAttempt' && log.attemptData.attempt.qGroupID === aGroup.qGroupID) {
			// convenience method to make an ordered array of questionIds
			log.attemptData.attempt.questionOrder = log.attemptData.attempt.qOrder
				? log.attemptData.attempt.qOrder.split(',').map(id => parseInt(id, 10)) // alternates in use
				: aGroup.kids.map(q => q.questionID) // order is just as it is in the LO

			foundLogs.push(log.attemptData)
		}
	})
	return foundLogs
}

const ModalScoreDetailsWithAPI = ({onClose, userName, userID, instID, loID}) => {
	const { isError: isVisitDataError, data: visitData, isFetching: isVisitDataFetching } = useQuery(['visitTrackingData', userID, instID], apiGetVisitTrackingData, {
		initialStale: true,
		initialData: null,
		staleTime: Infinity
	})

	// note, can return cached value before visitData loads
	const { isError: isLOError, data: loData, isFetching: isLOFetching } = useQuery(['getLO', loID], apiGetLO, {
		initialStale: true,
		staleTime: Infinity,
		initialData: null,
		enabled: visitData
	})

	// merge some api states
	const isFetching = isVisitDataFetching || isLOFetching
	const isError = isVisitDataError || isLOError
	const ready = !isFetching && loData && visitData

	const props = React.useMemo(() => {
		if(isFetching || isError || !visitData || !loData) return {}
		const visitLogs = visitData.visitLog.map(vLog => vLog.logs).flat()
		const attemptLogs = extractAssessmentAttemptData(visitLogs, loData.aGroup)
		return { userName, attemptLogs, aGroup: loData.aGroup}
	}, [onClose, visitData, loData, isFetching])

	if(!ready) return <div>Loading</div>
	if(isError) return <div>Error Loading Data</div>
	return <ModalScoreDetails {...props} onClose={onClose} />
}

const getModal = (modalType, modalProps, onCloseModal) => {
	switch (modalType) {
		case 'aboutThisLO':
			return <ModalAboutLOWithAPI onClose={onCloseModal} loID={modalProps.loID} />

		case 'instanceDetails':
			return <ModalInstanceDetails {...modalProps} onClose={onCloseModal} />

		case 'scoreDetails':
			return <ModalScoreDetailsWithAPI {...modalProps} onClose={onCloseModal} />

		case 'scoresByQuestion':
			return <ModalScoresByQuestion {...modalProps} onClose={onCloseModal} />

		case 'aboutObojoboNext':
			return <ModalAboutObojoboNext {...modalProps} onClose={onCloseModal} />

		default:
			return null
	}

}

const RepositoryModals = ({ instanceName, modalType, modalProps, onCloseModal }) => {
	useEffect(() => {
		ReactModal.setAppElement('#repository')
	})

	const modal = getModal(modalType, modalProps, onCloseModal)

	return modal ? (
		<ReactModal
			isOpen={true}
			contentLabel={instanceName}
			className={`repository--modal ${modalType}`}
			overlayClassName="repository--modal-overlay"
			onRequestClose={onCloseModal}
		>
			<div>
				<div className="top-bar">
					<div className="module-title">{instanceName}</div>
					<Button type="text" ariaLabel="Close" onClick={onCloseModal} text="×" />
				</div>
				<div className="modal-content">{modal}</div>
			</div>
		</ReactModal>
	) : null
}

ReactModal.setAppElement('#react-app');

export default RepositoryModals
