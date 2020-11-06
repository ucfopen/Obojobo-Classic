import './repository-page.scss'

import React, { useState, useCallback, useEffect } from 'react'
import { useQuery, useQueryCache } from 'react-query'
import { apiGetInstances, apiGetLO } from '../util/api'
import MyInstances from './my-instances'
import InstanceSection from './instance-section'
import Header from './header'
import RepositoryModals from './repository-modals'

const RepositoryPage = () => {
	const queryCache = useQueryCache()
	const reloadInstances = useCallback(() => {
		queryCache.invalidateQueries('getInstances')
		setSelectedInstanceIndex(null)
	}, null)
	const { isError, data, error } = useQuery('getInstances', apiGetInstances, {
		initialStale: true,
		staleTime: 0
	})
	const [selectedInstanceIndex, setSelectedInstanceIndex] = useState(null)
	const [modal, setModal] = useState(null)

	if (isError) return <span>Error: {error.message}</span>

	const selectedInstance = selectedInstanceIndex === null ? null : data[selectedInstanceIndex]

	const onClickAboutThisLO = async () => {
		//@TODO: Is this how to do this?
		const loMeta = await apiGetLO(selectedInstance.loID)

		setModal({
			type: 'aboutThisLO',
			props: {
				learnTime: loMeta.learnTime,
				language: loMeta.language,
				numContentPages: loMeta.summary.contentSize,
				numPracticeQuestions: loMeta.summary.practiceSize,
				numAssessmentQuestions: loMeta.summary.assessmentSize,
				authorNotes: loMeta.notes,
				learningObjective: loMeta.objective
			}
		})
	}

	const onClickEditInstanceDetails = () => {
		alert('onClickEditInstanceDetails')
	}

	const onClickManageAccess = () => {
		alert('onClickManageAccess')
	}

	const onClickDownloadScores = () => {
		alert('onClickDownloadScores')
	}

	const onClickViewScoresByQuestion = () => {
		alert('onClickViewScoresByQuestion')
	}

	const onClickHeaderAboutOrBannerLink = () => {
		alert('onClickHeaderAboutOrBannerLink')
	}

	const onClickHeaderCloseBanner = () => {
		alert('onClickHeaderCloseBanner')
	}

	const onClickLogOut = () => {
		alert('onClickLogOut')
	}

	const onClickPreview = () => {
		if (selectedInstanceIndex === null) {
			return
		}

		window.open(`/preview/${selectedInstance.loID}`, '_blank')
	}

	return (
		<div id="repository" className="repository">
			<Header
				onClickAboutOrBannerLink={onClickHeaderAboutOrBannerLink}
				onClickCloseBanner={onClickHeaderCloseBanner}
				onClickLogOut={onClickLogOut}
			/>
			<main>
				<div className="wrapper">
					<MyInstances
						instances={data}
						selectedInstanceIndex={selectedInstanceIndex}
						onSelect={row => setSelectedInstanceIndex(row)}
						onClickRefresh={() => reloadInstances()}
					/>
					<InstanceSection
						onClickAboutThisLO={onClickAboutThisLO}
						onClickPreview={onClickPreview}
						onClickEditInstanceDetails={onClickEditInstanceDetails}
						onClickManageAccess={onClickManageAccess}
						onClickDownloadScores={onClickDownloadScores}
						onClickViewScoresByQuestion={onClickViewScoresByQuestion}
						instance={selectedInstanceIndex !== null ? data[selectedInstanceIndex] : null}
					/>
				</div>
			</main>
			{modal ? (
				<RepositoryModals
					modalType={modal.type}
					modalProps={modal.props}
					onCloseModal={() => setModal(null)}
				/>
			) : null}
		</div>
	)
}

export default RepositoryPage
