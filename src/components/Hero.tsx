import { Button } from "@/components/ui/button";

const Hero = () => {
  const scrollToContact = () => {
    document.getElementById('contact')?.scrollIntoView({ behavior: 'smooth' });
  };

  return (
    <section 
      id="home" 
      className="relative bg-gradient-to-br from-primary/10 via-background to-secondary/20 py-20 lg:py-32 overflow-hidden"
    >
      {/* Premium Construction Background Image */}
      <div 
        className="absolute inset-0 bg-cover bg-center bg-no-repeat opacity-40"
        style={{
          backgroundImage: "url('https://images.unsplash.com/photo-1487958449943-2429e8be8625?ixlib=rb-4.0.3&auto=format&fit=crop&w=6000&q=80')"
        }}
      />
      
      <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 z-10">
        <div className="text-center">
          <h1 className="text-4xl md:text-6xl font-bold text-foreground mb-6">
            Premium Construction
            <span className="block text-primary">Excellence</span>
          </h1>
          <p className="text-xl text-muted-foreground mb-8 max-w-3xl mx-auto">
            Building dreams with precision, quality, and trust. Vasu Builder brings over years of expertise 
            in construction, delivering exceptional residential and commercial projects across Tamil Nadu.
          </p>
          
          {/* About Our Services Section */}
          <div className="bg-white/90 backdrop-blur-sm rounded-lg p-8 mb-8 max-w-4xl mx-auto shadow-lg">
            <h2 className="text-2xl font-bold text-foreground mb-6">About Our Premium Services</h2>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6 text-left">
              <div>
                <h3 className="font-semibold text-primary mb-2">🏠 Residential Excellence</h3>
                <p className="text-muted-foreground text-sm">
                  Custom homes, luxury villas, and modern apartments built with premium materials and cutting-edge construction techniques.
                </p>
              </div>
              <div>
                <h3 className="font-semibold text-primary mb-2">🏢 Commercial Solutions</h3>
                <p className="text-muted-foreground text-sm">
                  Office complexes, retail spaces, and commercial buildings designed for functionality and business success.
                </p>
              </div>
              <div>
                <h3 className="font-semibold text-primary mb-2">🔨 Expert Renovation</h3>
                <p className="text-muted-foreground text-sm">
                  Complete home and office renovations that transform your existing spaces into modern, functional environments.
                </p>
              </div>
              <div>
                <h3 className="font-semibold text-primary mb-2">🎨 Interior Design</h3>
                <p className="text-muted-foreground text-sm">
                  Full interior design services that blend aesthetic appeal with practical functionality for every space.
                </p>
              </div>
            </div>
          </div>
          
          <div className="flex flex-col sm:flex-row gap-4 justify-center">
            <Button 
              size="lg" 
              className="text-lg px-8 py-3"
              onClick={scrollToContact}
            >
              Contact Us Today
            </Button>
            <Button 
              variant="outline" 
              size="lg" 
              className="text-lg px-8 py-3 bg-white/90 backdrop-blur-sm"
              onClick={() => document.getElementById('services')?.scrollIntoView({ behavior: 'smooth' })}
            >
              View Our Work
            </Button>
          </div>
        </div>
      </div>
      
      <div className="absolute inset-0 bg-gradient-to-r from-transparent via-primary/5 to-transparent"></div>
    </section>
  );
};

export default Hero;
