
import React from 'react'
import Header from '../components/Header'
import HeroSection from '../components/HeroSection'
import HotMoviesSection from '../components/HotMoviesSection'
import ActionMoviesSection from '../components/ActionMoviesSection'
import ComedyMoviesSection from '../components/ComedyMoviesSection'
import HorrorMoviesSection from '../components/HorrorMoviesSection'
import RomanceMoviesSection from '../components/RomanceMoviesSection'
import SciFiMoviesSection from '../components/SciFiMoviesSection'
import DramaMoviesSection from '../components/DramaMoviesSection'
import MovieBookingSection from '../components/MovieBookingSection'

export default function Home() {
  return (
    <div className="min-h-screen bg-black">
      <Header />
      <HeroSection />
      <MovieBookingSection />
      <HotMoviesSection />
      <ActionMoviesSection />
      <ComedyMoviesSection />
      <HorrorMoviesSection />
      <RomanceMoviesSection />
      <SciFiMoviesSection />
      <DramaMoviesSection />
    </div>
  )
}
